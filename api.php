<?php
/*
/api.php
Ne renvoie pas de HTML
Renvoie tout en json
actions possibles:
- add_game
- get_leaders_winrate
- get_players_winrate
- get_games
- get_decks
todo passer par une action api pour ajouter les games a la db
*/

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_REQUEST['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'pas d\'action fournie']);
    exit;
}
$action = $_REQUEST['action'];

try {
    $pdo = get_db_connection();
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible de se connecter à la base de données: ' . $error->getMessage()]);
    exit;
}

// Fonctions utilitaires
function repeat($value, int $times) {
    $array = [];
    for ($i = 0 ; $i < $times ; $i++) {
        $array[] = $value;
    }
    return $array;
}

switch ($action) {
    case 'add_game':
        // vérifier que toutes les données sont fournies
        if (
            !isset($_REQUEST['winner'])
            || !isset($_REQUEST['loser'])
            || !isset($_REQUEST['winningPlayer'])
        ) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Formulaire incomplet',
                'details' => [
                    'winner' => $_REQUEST['winner'] ?? null,
                    'loser' => $_REQUEST['loser'] ?? null,
                    'winningPlayer' => $_REQUEST['winningPlayer'] ?? null,
                ],
            ]);
            exit;
        }

        $winner = (int) $_REQUEST['winner'];
        $loser = (int) $_REQUEST['loser'];
        $LeandreWon = (int) ($_REQUEST['winningPlayer'] === 'Léandre');

        try {
            $stmt = $pdo->prepare('INSERT INTO games (winner, loser, LeandreWon) VALUES (?, ?, ?);');
            $stmt->execute([$winner, $loser, $LeandreWon]);
        } catch (Throwable $error) {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de l'insertion dans la db: $error"]);
            exit;
        }

        echo json_encode(['success' => true]);
        break;

    case 'get_leaders_winrate':
        try {
            $stmt = $pdo->query('SELECT winner, loser FROM games');
            $games = $stmt->fetchAll();
        } catch (Throwable $error) {
            http_response_code(500);
            echo json_encode(['error' => "erreur lors de la requete du winrate: $error"]);
            exit;
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
    
    case 'get_players_winrate':
        try {
            $stmt = $pdo->query('SELECT LeandreWon FROM games');
            $rows = $stmt->fetchAll();
        } catch (Throwable $error) {
            http_response_code(500);
            echo json_encode(['error' => "erreur lors de la requete du winrate: $error"]);
            exit;
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
        $winningLeader = isset($_REQUEST['winningLeader']) ? $_REQUEST['winningLeader'] : null;
        $request = 'SELECT winner, loser, LeandreWon FROM games WHERE 1=1';
        if (isset($_REQUEST['leader1'])) {
            $leader1 = (int) $_REQUEST['leader1'];
            switch ($winningLeader) {
                case 'l1won':
                    $request .= " AND winner = $leader1";
                    break;
                case 'l2won':
                    $request .= " AND loser = $leader1";
                    break;
                case null:
                    $request .= " AND (winner = $leader1 OR loser = $leader1)";
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'winningLeader contient une valeur inconnue: ' . $winningLeader]);
                    exit;
            }
        }
        if (isset($_REQUEST['leader2'])) {
            $leader2 = (int) $_REQUEST['leader2'];
            switch ($winningLeader) {
                case 'l1won':
                    $request .= " AND loser = $leader2";
                    break;
                case 'l2won':
                    $request .= " AND winner = $leader2";
                    break;
                case null:
                    $request .= " AND (winner = $leader2 OR loser = $leader2)";
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'winningLeader contient une valeur inconue: ' . $winningLeader]);
                    exit;
            }
        }
        $request .= ';';
        try {
            $stmt = $pdo->query($request);
            $games = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $games]);
        } catch (Throwable $error) {
            http_response_code(500);
            echo json_encode(['error' => $error->getMessage()]);
            exit;
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
                http_response_code(500);
                echo json_encode(['error' => $error->getMessage()]);
                exit;
            }
            $decks = $stmt->fetchAll();
            echo json_encode(['success' => true, 'decks' => $decks]);
            exit;
        } else {
            // TODO
            exit;
        }

    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'action inconnue']);
        exit;
}