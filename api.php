<?php
/*
/api.php
Ne renvoie pas de HTML
Renvoie tout en json
actions possibles:
- get_leaders_winrate
- get_players_winrate
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

$pdo = get_db_connection();

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
            $gamesPlayed[$winner - 1] ++;
            $gamesPlayed[$loser - 1] ++;
        }
        $winrates = repeat(0, 18);
        for ($i=0 ; $i < 18 ; $i++) {
            $winrates[$i] = $gamesPlayed[$i] > 0 ? $wins[$i] / $gamesPlayed[$i] : 0;
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
        $winrateLeandre = $games > 0 ? $victorys / $games : 0;
        $winrateLancelot = 1 - $winrateLeandre;
        echo json_encode(['success' => true, 'winrateLeandre' => $winrateLeandre, 'winrateLancelot' => $winrateLancelot]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'action inconnue']);
        exit;
}