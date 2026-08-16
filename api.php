<?php
/*
/api.php
Ne renvoie pas de HTML
Renvoie tout en json
actions possibles:
- add_game
- get_decks_winrate
- get_players_winrate
- get_games
- get_decks
- add_deck
*/

function error(int $code, string $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_REQUEST['action'])) {
    error(400, 'pas d\'action fournie');
}
$action = $_REQUEST['action'];

try {
    $pdo = get_db_connection();
} catch (Throwable $error) {
    error(500, 'Impossible de se connecter à la base de données: ' . $error->getMessage());
}

// Fonctions utilitaires 

function repeat(mixed $value, int $times) {
    $array = [];
    for ($i = 0 ; $i < $times ; $i++) {
        $array[] = $value;
    }
    return $array;
}

function verifyParams(array $params) {
    foreach ($params as $param) {
        if (!isset($_REQUEST[$param])) {
            error(400, 'paramètre ' . $param . ' manquant');
        }
    }
}

switch ($action) {
    case 'add_game':
        verifyParams(['winner', 'loser', 'winningPlayer']);

        $winner = (int) $_REQUEST['winner'];
        $loser = (int) $_REQUEST['loser'];
        $LeandreWon = (int) ($_REQUEST['winningPlayer'] === 'Léandre');

        try {
            $stmt = $pdo->prepare('INSERT INTO games (winner, loser, LeandreWon) VALUES (?, ?, ?);');
            $stmt->execute([$winner, $loser, $LeandreWon]);
        } catch (Throwable $error) {
            error(500, "Erreur lors de l'insertion dans la db: $error");
        }

        echo json_encode(['success' => true]);
        break;

    case 'get_decks_winrate':
        try {
            $stmt = $pdo->query('SELECT deck_id, winrate FROM decks_winrates');
            $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $winrates = [];
            foreach ($games as $game) {
                $winrates[(int) $game['deck_id']] = (float) $game['winrate'];
            }

            echo json_encode(['success' => true, 'winrates' => $winrates]);
            break;
        } catch (Throwable $error) {
            error(500, "erreur lors de la requete du winrate: $error");
        }
    
    case 'get_players_winrate':
        try {
            $stmt = $pdo->query('SELECT nb_games, winrateLeandre, winrateLancelot FROM players_winrates');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                echo json_encode(['success' => true, 'winrateLeandre' => 0, 'winrateLancelot' => 0, 'nb_games' => 0]);
                break;
            }
        } catch (Throwable $error) {
            error(500, "erreur lors de la requete du winrate: $error");
        }

        echo json_encode([
            'success' => true,
            'winrateLeandre' => (float) ($row['winrateLeandre'] ?? 0),
            'winrateLancelot' => (float) ($row['winrateLancelot'] ?? 0),
            'nb_games' => (int) ($row['nb_games'] ?? 0),
        ]);
        break;
    
    case 'get_games':
        verifyParams(['deck1', 'deck2', 'winning_deck', 'last_version_only', 'winning_player']);

        $deck1 = isset($_REQUEST['deck1']) && is_numeric($_REQUEST['deck1']) ? (int) $_REQUEST['deck1'] : null;
        $deck2 = isset($_REQUEST['deck2']) && is_numeric($_REQUEST['deck2']) ? (int) $_REQUEST['deck2'] : null;
        $winning_deck = isset($_REQUEST['winning_deck']) && is_numeric($_REQUEST['winning_deck']) ? (int) $_REQUEST['winning_deck'] : null;
        $last_version_only = in_array($_REQUEST['last_version_only'], [0, 1]) ? $_REQUEST['last_version_only'] : 0;
        $winning_player = $_REQUEST['winning_player'] ?? null;
        if ($winning_player === 'all' || $winning_player === 'null') {
            $winning_player = null;
        }

        try {
            $values = [
                $deck1,
                $deck2,
                $winning_deck,
                $last_version_only,
                $winning_player,
            ];

            $quotedValues = array_map(function ($value) use ($pdo) {
                if ($value === null) {
                    return 'NULL';
                }

                if (is_int($value) || is_float($value)) {
                    return (string) $value;
                }

                return $pdo->quote((string) $value);
            }, $values);

            $stmt = $pdo->query('CALL search_games(' . implode(', ', $quotedValues) . ')');
            if ($stmt === false) {
                throw new RuntimeException('La procédure search_games a échoué.');
            }

            $games = $stmt->fetchAll();
        } catch (Throwable $error) {
            error(500, 'Erreur lors de la requete SQL: ' . $error->getMessage());
        }
        echo json_encode(['success' => true, 'data' => $games]);
        break;
    
    case 'get_cards':
        try {
            $stmt = $pdo->query('SELECT id, name FROM cartes ORDER BY name ASC');
            $cards = $stmt->fetchAll();
            echo json_encode(['success' => true, 'cards' => $cards]);
        } catch (Throwable $error) {
            error(500, $error->getMessage());
        }
        break;

    case 'get_decks':
        // renvoyer tous les decks si l'id n'est pas indiqué
        if (!isset($_REQUEST['deck_id'])) {
            try {
                $stmt = $pdo->query('
                    SELECT decks.*,
                    leaders.name AS leaderName,
                    baseColor.colorName AS baseColorName, baseColor.officialName AS baseColorOfficialName
                    FROM decks
                    LEFT JOIN leaders ON decks.leaderId = leaders.id
                    LEFT JOIN baseColor ON decks.baseColorId = baseColor.id
                ');
            } catch (Throwable $error) {
                error(500, $error->getMessage());
            }
            $decks = $stmt->fetchAll();
            echo json_encode(['success' => true, 'decks' => $decks]);
            break;
        } else {
            // TODO
            exit;
        }
    
    case 'add_deck':
        verifyParams(['deck', 'name', 'leader_id', 'base_color_id']);
        $deck = json_decode($_REQUEST['deck'], true);
        if (!is_array($deck)) {
            error(400, 'Le paramètre deck doit être un objet JSON valide.');
        }

        $name = $_REQUEST['name'];
        $leader_id = (int) $_REQUEST['leader_id'];
        $base_color_id = (int) $_REQUEST['base_color_id'];
        $version = isset($_REQUEST['version']) ? (int) $_REQUEST['version'] : 1;

        try {
            // Ajouter le deck à la db
            $stmt = $pdo->prepare('INSERT INTO decks (name, leaderId, baseColorId, version) VALUES (?, ?, ?, ?);');
            $stmt->execute([
                $name,
                $leader_id,
                $base_color_id,
                $version
            ]);
            $deck_id = (int) $pdo->lastInsertId();

            // Ajouter les cartes à la db
            foreach ($deck as $card_id => $quantity) {
                $stmt = $pdo->prepare('INSERT INTO cartes_dans_decks (cardId, deckId, exemplaires) VALUES (?, ?, ?);');
                $stmt->execute([
                    $card_id,
                    $deck_id,
                    $quantity
                ]);
            }
            
            echo json_encode(['success' => true, 'deck_id' => $deck_id]);
        } catch (Throwable $error) {
            error(500, 'erreur lors de l\'insersion du deck: ' . $error->getMessage());
        }
        break;

    default:
        error(400, 'action inconnue');
}