<?php
/*
/api.php
Ne renvoie pas de HTML
Renvoie tout en json
actions possibles:
- get_leaders_winrate
- get_players_winrate
- get_games
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
    echo json_encode([
        'success' => false,
        'error' => 'Impossible de se connecter à la base de données: ' . $error->getMessage()
    ]);
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
        // Vérifier que la requete est correcte
        if (isset($_REQUEST['winningLeader'])) {
            $winningLeader = $_REQUEST['winningLeader'];
            if ($winningLeader === 'l1won' && !isset($_REQUEST['leader1'])) {
                http_response_code(400);
                echo json_encode(['error' => 'parametres incompatibles']);
                exit;
            }
            if ($winningLeader === 'l2won' && !isset($_REQUEST['leader2'])) {
                http_response_code(400);
                echo json_encode(['error' => 'parametres incompatibles']);
                exit;
            }
        }
        $request = 'SELECT winner, loser, LeandreWon FROM games WHERE 1=1';
        if (isset($_REQUEST['leader1'])) {
            $leader1 = (int) $_REQUEST['leader1'];
            if (isset($_REQUEST['winningLeader']) && $_REQUEST['winningLeader'] === 'l1won') {
                $request .= " AND winner = $leader1";
            } else {
                $request .= " AND (winner = $leader1 OR loser = $leader1)";
            }
        }
        if (isset($_REQUEST['leader2'])) {
            $leader2 = $_REQUEST['leader2'];
            if (isset($_REQUEST['winningLeader']) && $_REQUEST['winningLeader'] === 'l2won') {
                $request .= " AND winner = $leader2";
            } else {
                $request .= " AND (winner = $leader2 OR loser = $leader2)";
            }
        }
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
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'action inconnue']);
        exit;
}