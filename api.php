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

if (!isset($_REQUEST['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'pas d\'action fournie']);
    exit;
}
$action = $_REQUEST['action'];

$pdo = get_db_connection();

switch ($action) {
    case 'get_leaders_winrate':
        try {
            $games = $pdo->query('SELECT winner, loser FROM games');
        } catch (Throwable $error) {
            http_response_code(500);
            echo json_encode(['error' => "erreur lors de la requete du winrate: $error"]);
            exit;
        }
        $wins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $games = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($games as $game) {
            $winner = $game->winner;
            $loser = $game->loser;
            $wins[$winner - 1] ++;
            $games[$winner - 1] ++;
            $games[$loser - 1] ++;
        }
        $winrates = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        for ($i=0 ; $i < 18 ; $i++) {
            $winrates[$i] = $wins[$i] / $games[$i];
        }

        echo json_encode(['success' => true, 'winrates' => $winrates]);
        break;
    
    case 'get_players_winrate':
        try {
            $LeandreWon = $pdo->query('SELECT LeandreWon FROM games');
        } catch (Throwable $error) {
            http_response_code(500);
            echo json_encode(['error' => "erreur lors de la requete du winrate: $error"]);
            exit;
        }
        $victorys = 0;
        $games = 0;
        foreach($LeandreWon as $LeandreWon) {
            if ($LeandreWon) {
                $victorys ++;
            }
            $games ++;
        }
        $winrateLeandre = $victorys/$games;
        $winrateLancelot = 1 - $winrateLeandre;
        echo json_encode(['success' => true, 'winrateLeandre' => $winrateLeandre, 'winrateLancelot' => $winrateLancelot]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'action inconnue']);
        exit;
}