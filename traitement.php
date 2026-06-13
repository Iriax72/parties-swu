<?php
// Fonction utilitaire
function logInJs (string $message) :void {
    $output = $message;
    if (isarray($output)) {
        $output = implode(',', $output);
    }
    echo "<script>console.log('log depuis php: $message');</script>";
}

// Vérifier que tous les champs sont remplis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['winner'])
        || !isset($_POST['loser'])
        || !isset($_POST['winningPlayer'])
    ) {
        logInJs("
            Formulaire incomplet reçu par traitement.php:
            \nwinner: {$_POST['winner']}
            \nloser: {$_POST['loser']}
            \nwinningPlayer: {$_POST['winningPlayer']}.
        ");
        exit;
    }
}
$winner = $_POST['winner'];
$loser = $_POST['loser'];
$LeandreWon = $_POST['winningPlayer'] === 'Léandre' ? true : false;

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();

// Insérer les nouvelles données dans la db
$stmt = $pdo->prepare('INSERT INTO games (winner, loser, LeandreWon) VALUES (?, ?, ?);');
$stmt->execute([$winner, $loser, $LeandreWon]);