<?php
/*
/pages/searchGame.php

Permet un questionement approfondi de la db
*/

// Vérifie que la db soir correctement inktialisée
require_once __DIR__ . '/../config.php';
try {
    init_db();
} catch (Throwable $error) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo '<div class="error">' . htmlspecialschar($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}

// Obtenir la liste des leaders depuis /datas.json
$datas = file_get_contents(__DIR__ . '/../datas.json');
$decoded_datas = json_decode($datas);
$leader_names = $decoded_datas->leaders;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de partie SWU</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/searchGame.css">
    <script src="/js/searchGame.js" defer></script>
</head>
<body>
    <button type="button" id="back-btn" class="btn btn2">BACK</button>
    <form class="form">
        <label for="leader1" class="label">Selectionner un leader</label>
        <select name="leader1" id="select1" class="select">
            <option value="all">tous</option>
            <?php
            foreach ($leader_names as $id => $name) {
                echo "<option value=\"$id\">$name</option>";
            }
            ?>
        </select>
        <input type="radio" name="winningLeader" id="l1won" value="l1won">
        <label for="l1won" class="label">Parties gagnées par ce leader</label>
        <br>
        <label for="leader2" class="label">Selectionner un autre leader</label>
        <select name="leader2" id="select2" class="select">
            <option value="all">tous</option>
            <?php
            foreach ($leader_names as $id => $name) {
                echo "<option value=\"$id\">$name</option>";
            }
            ?>
        </select>
        <input type="radio" name="winningLeader" id="l2won" value="l2won">
        <label for="l2won" class="label">Parties gagnées par ce leader</label>
        <br>
        <input type="radio" name="winningLeader" id="nobodyWon" value="nobodyWon" checked>
        <label for="nobodyWon" class="label">Chercher indépendament du gagant</label>
        <br><br><br>
        <button type="submit" id="submit-btn" class="btn btn3">RECHERCHER</button>
    </form>
</body>
</html>