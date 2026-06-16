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
    echo '<div class="error">' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}

// Obtenir la liste des leaders depuis /datas.json
$datas = file_get_contents(__DIR__ . '/../datas.json');
$decoded_datas = json_decode($datas);
$leader_names = $decoded_datas->leaders;

// Élement DOM
function leader_select(array $leader_names, string $name, string $id): string {
    $ret = "<select name=\"$name\" id=\"$id\" class=\"select\">";
    $ret .= '<option value="all">tous les leaders</option>';
    foreach ($leader_names as $l_id => $l_name) {
        $ret .= "<option value=\"$l_id\">$l_name</option>";
    }
    $ret .= '</select>';
    return $ret;
}
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
    <?php/*
    <form class="form">
        <label for="leader1" class="label">Selectionner un leader</label>
        <?= leader_select('leader1', 'select2') ?>
        <input type="radio" name="winningLeader" id="l1won" value="l1won">
        <label for="l1won" class="label">Parties gagnées par ce leader</label>
        <br>
        <label for="leader2" class="label">Selectionner un autre leader</label>
        <?= leader_select('leader2', 'select3') ?>
        <input type="radio" name="winningLeader" id="l2won" value="l2won">
        <label for="l2won" class="label">Parties gagnées par ce leader</label>
        <br>
        <input type="radio" name="winningLeader" id="nobodyWon" value="nobodyWon" checked>
        <label for="nobodyWon" class="label">Chercher indépendament du gagant</label>
        <br><br><br>
        <button type="submit" id="submit-btn" class="btn btn3">RECHERCHER</button>
    </form>
    */?>
    <form class="form">
        <span class="text">Rechercher les</span>
        <select name="result" id="select1">
            <option value="victory">victoires</option>
            <option value="lose">défaites</option>
            <option value="games">parties</option>
        </select>
        <span class="text">de</span>
        <?= leader_select($leader_names, 'leader1', 'select2'); ?>
        <span>contre</span>
        <?= leader_select($leader_names, 'leader2', 'select3'); ?>
        <span class="text">.</span>
        <br>
        <button type="submit" id="submit-btn" class="btn btn3">RECHERCHER</button>
    </form>
</body>
</html>