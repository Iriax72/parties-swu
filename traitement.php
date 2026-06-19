<?php
/*
// Vérifier que tous les champs sont remplis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['winner'])
        || !isset($_POST['loser'])
        || !isset($_POST['winningPlayer'])
    ) {
        echo "
            Formulaire incomplet reçu par traitement.php:
            \nwinner: {$_POST['winner']}
            \nloser: {$_POST['loser']}
            \nwinningPlayer: {$_POST['winningPlayer']}.
        ";
        exit;
    }
}
$winner = $_POST['winner'];
$loser = $_POST['loser'];
$LeandreWon = (int) ($_POST['winningPlayer'] === 'Léandre');

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();

// Insérer les nouvelles données dans la db
try {
    $stmt = $pdo->prepare('INSERT INTO games (winner, loser, LeandreWon) VALUES (?, ?, ?);');
    $stmt->execute([$winner, $loser, $LeandreWon]);
    header('Location: /pages/addGame.php');
    exit;
} catch (Throwable $error) {
    echo "Erreur lors de l'insertion de la partie: $error";
}
*/