<?php
/*
pages/addGame.php

Permet d'ajouter des parties à la db
*/

// Vérifie que la db soit correctement initialisée pour le cas où cette page ait directement été appelée sans passer par l'index
require_once __DIR__ . '/../config.php';
try {
    init_db();
} catch (Throwable $error) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo '<div class="error">' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}

// Obtenir la liste des leaders depuis /datas.json
$datas = file_get_contents('/datas.json');
$decoded_datas = json_decode($datas, false);
$leader_names = $decoded_datas->leaders;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parties SWU, Ajouter une partie</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/addGame.css">
    <script src="/js/addGame.js" defer></script>
</head>
<body>
    <button class="btn btn2" type="button" id="back-btn">BACK</button>
    <form action="/traitement.php" method="POST">
        <select name="winner" id="winner">
            <?php
            foreach ($leader_names as $id => $name) {
                echo "<option value=\"$id\">$name</option>";
            }
            ?>
        </select>
        <select name="loser" id="loser">
            <?php
            foreach ($leader_names as $id => $name) {
                echo "<option value=\"$id\">$name</option>";
            }
            ?>
        </select>
        <input type="radio" name="winningPlayer" id="Léandre" value="Léandre">
            <label for="Leandre">Léandre</label>
        <input type="radio" name="winningPlayer" id="Lancelot" value="Lancelot">
            <label for="Lancelot">Lancelot</label>
        <button type="submit" id="submitBtn" class="btn btn3">Ajouter la partie</button>
    </form>
</body>
</html>