<?php
/*
/api.php
Ne renvoie pas de HTML
Renvoie tout en json
actions possibles:
- add_game
(- get_leaders_winrate)
- get_decks_winrate
- get_players_winrate
- get_games
- get_decks
- add_deck
todo passer par une action api pour ajouter les games a la db
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

    /* case 'get_leaders_winrate':
        try {
            $stmt = $pdo->query('SELECT winner, loser FROM games');
            $games = $stmt->fetchAll();
        } catch (Throwable $error) {
            error(500, "erreur lors de la requete du winrate: $error");
        }
        $wins = repeat(0, 18);
        $gamesPlayed = repeat(0, 18);
        foreach ($games as $game) {
            $winner = (int) $game['winner'];
            $loser = (int) $game['loser'];
            $wins[$winner - 1] ++;
            $gamesPlayed[$winner - 1] ++;
            $gamesPlayed[$loser - 1] ++;
        }
        $winrates = repeat(0, 18);
        for ($i=0 ; $i < 18 ; $i++) {
            $winrates[$i] = $gamesPlayed[$i] > 0 ? $wins[$i] / $gamesPlayed[$i] : -1;
        }

        echo json_encode(['success' => true, 'winrates' => $winrates]);
        break;
    */
    case 'get_decks_winrate':
        try {
            $stmt = $pdo->query('
            WITH decks_games AS (
                SELECT
                    winning_deck.id AS deck_id,
                    winning_deck.name AS deck_name,
                    1 AS isWin
                FROM games g
                JOIN decks winning_deck ON g.winner = winning_deck.id

                UNION ALL

                SELECT
                    losing_deck.id AS deck_id,
                    losing_deck.name AS deck_name,
                    0 AS isWin
                FROM games g
                JOIN decks losing_deck ON g.loser = losing_deck.id
            )

            SELECT
                dg.deck_id AS deck_id,
                dg.deck_name AS deck_name,
                COUNT(*) AS total_games,
                SUM(dg.isWin) AS total_wins,
                COUNT(*) - SUM(dg.isWin) AS total_losses,
                ROUND(SUM(dg.isWin) / COUNT(*), 4) AS winrate
            FROM decks_games dg
            GROUP BY dg.deck_id, dg.deck_name
            ORDER BY winrate DESC
            ');
            $games = $stmt->fetchAll();
            $winrates = [];
            foreach ($games as $game) {
                $winrates[$game['deck_id']] = (float) $game['winrate'];
            }

            echo json_encode(['success' => true, 'winrates' => $winrates]);
            break;
        } catch (Throwable $error) {
            error(500,  "erreur lors de la requete du winrate: $error");
            
        }
    
    case 'get_players_winrate':
        try {
            $stmt = $pdo->query('SELECT LeandreWon FROM games');
            $rows = $stmt->fetchAll();
        } catch (Throwable $error) {
            error(500, "erreur lors de la requete du winrate: $error");
        }
        $victorys = 0;
        $games = 0;
        foreach ($rows as $row) {
            if ((bool) $row['LeandreWon']) {
                $victorys ++;
            }
            $games ++;
        }
        $winrateLeandre = $games > 0 ? $victorys / $games : -1;
        $winrateLancelot = 1 - $winrateLeandre;
        echo json_encode(['success' => true, 'winrateLeandre' => $winrateLeandre, 'winrateLancelot' => $winrateLancelot]);
        break;
    
    case 'get_games':
            verifyParams(['deck1', 'deck2', 'winning_deck', 'last_version_only']);
        try {
            $deck1 = isset($_REQUEST['deck1']) && is_numeric($_REQUEST['deck1']) ? (int) $_REQUEST['deck1'] : null;
            $deck2 = isset($_REQUEST['deck2']) && is_numeric($_REQUEST['deck2']) ? (int) $_REQUEST['deck2'] : null;
            $winning_deck = isset($_REQUEST['winning_deck']) && is_numeric($_REQUEST['winning_deck']) ? (int) $_REQUEST['winning_deck'] : null;
            $last_version_only = in_array($_REQUEST['last_version_only'], [0, 1]) ? $_REQUEST['last_version_only'] : 0;

        } catch (Throwable $error) {
            error(500, $error->getMessage());
        }
        /*
        if (!$historical) {
            try {
                $findDeckInfoStmt = $pdo->prepare('SELECT leaderId, baseColorId, name FROM decks WHERE id = :id LIMIT 1');
                $findLatestDeckStmt = $pdo->prepare(
                    'SELECT id FROM decks WHERE leaderId = :leaderId AND baseColorId = :baseColorId AND name = :name ORDER BY version DESC LIMIT 1'
                );

                if ($deck1 !== null) {
                    $findDeckInfoStmt->execute([':id' => $deck1]);
                    $deckInfo = $findDeckInfoStmt->fetch(PDO::FETCH_ASSOC);
                    if ($deckInfo !== false) {
                        $findLatestDeckStmt->execute($deckInfo);
                        $latestDeckId = $findLatestDeckStmt->fetchColumn();
                        if ($latestDeckId !== false) {
                            $deck1 = (int) $latestDeckId;
                        }
                    }
                }
                if ($deck2 !== null) {
                    $findDeckInfoStmt->execute([':id' => $deck2]);
                    $deckInfo = $findDeckInfoStmt->fetch(PDO::FETCH_ASSOC);
                    if ($deckInfo !== false) {
                        $findLatestDeckStmt->execute($deckInfo);
                        $latestDeckId = $findLatestDeckStmt->fetchColumn();
                        if ($latestDeckId !== false) {
                            $deck2 = (int) $latestDeckId;
                        }
                    }
                }
            } catch (Throwable $error) {
                error(500, 'Impossible de déterminer la version la plus récente des decks: ' . $error->getMessage());
            }
        }

        $query = '
            SELECT
                g.winner,
                g.loser,
                g.LeandreWon,
                winner_deck.name AS winnerName,
                loser_deck.name AS loserName
            FROM games g
            JOIN decks winner_deck ON g.winner = winner_deck.id
            JOIN decks loser_deck ON g.loser = loser_deck.id
            WHERE 1=1';
        $params = [];

        if ($deck1) {
            switch ($winningLeader) {
                case 'l1won':
                    $query .= ' AND g.winner = :deck1';
                    $params[':deck1'] = $deck1;
                    break;
                case 'l2won':
                    $query .= ' AND g.loser = :deck1';
                    $params[':deck1'] = $deck1;
                    break;
                case null:
                    $query .= ' AND (g.winner = :deck1A OR g.loser = :deck1B)';
                    $params[':deck1A'] = $deck1;
                    $params[':deck1B'] = $deck1;
                    break;
                default:
                    error(400, 'winningLeader contient une valeur inconnue: ' . $winningLeader);
            }
        }
        if ($deck2) {
            switch ($winningLeader) {
                case 'l1won':
                    $query .= ' AND g.loser = :deck2';
                    $params[':deck2'] = $deck2;
                    break;
                case 'l2won':
                    $query .= ' AND g.winner = :deck2';
                    $params[':deck2'] = $deck2;
                    break;
                case null:
                    $query .= ' AND (g.winner = :deck2A OR g.loser = :deck2B)';
                    $params[':deck2A'] = $deck2;
                    $params[':deck2B'] = $deck2;
                    break;
                default:
                    error(400, 'winningLeader contient une valeur inconue: ' . $winningLeader);
            }
        }
        $query .= ';';

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $games = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $games]);
        } catch (Throwable $error) {
            error(500, $error->getMessage());
        }
        */
        try {
            $request = file_get_contents(__DIR__ . '/sql/search_games.sql');
            if ($request === false) {
                throw new Error('le fichier est illisible');
            }
            $stmt = $pdo->prepare($request);
            /*if (!isset($deck1) || !isset($deck2) || !isset($winning_deck) || !isset($last_version_only)) {
                throw new Error('params incomolets: ' . $deck1. $deck2 . $winning_deck . $last_version_only);
            }*/
            try {
            $stmt->execute([
                $last_version_only,    // 1: ? = 0
                $winning_deck,         // 2: WHEN ? = 1
                $deck1,                // 3: (? IS NULL
                $deck1,                // 4: OR games.winner = ?)
                $deck2,                // 5: (? IS NULL
                $deck2,                // 6: OR games.loser = ?)
                $winning_deck,         // 7: WHEN ? = 2
                $deck2,                // 8: (? IS NULL
                $deck2,                // 9: OR games.winner = ?)
                $deck1,                // 10: (? IS NULL
                $deck1,                // 11: OR games.loser = ?)
                $deck1,                // 12: (? IS NULL
                $deck1,                // 13: OR games.winner = ?)
                $deck2,                // 14: (? IS NULL
                $deck2,                // 15: OR games.loser = ?)
                $deck2,                // 16: (? IS NULL
                $deck2,                // 17: OR games.winner = ?)
                $deck1,                // 18: (? IS NULL
                $deck1,                // 19: OR games.loser = ?)
            ]);
            } catch (Throwable $e) {
                throw new Error($e->getMessage() . '(erreur à la ligne $stmt->execute([...]);)' . "\ndeck1: $deck1, deck2: $deck2, winning_deck: $winning_deck, last_version_only: $last_version_only");
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