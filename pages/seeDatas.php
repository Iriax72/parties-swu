<?php
/*
pages/seeDatas.php

Permet de voir le contenu de la db
*/

// Vérifie que la db soit correctment initialisée pour le cas ou cette page ait directement été appelée sans passer par l'index
require_once __DIR__ . '/../config.php';
try {
    init_db();
} catch (Throwable $error) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo '<div class="error">' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parties SWU, Voir les données</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/seeDatas.css">
    <script src="/js/seeDatas.js" defer></script>
</head>
<body>
    <button class="btn btn2" type="button" id="back-btn">BACK</button>
    Oui, ça marche (TODO: virer ça)
</body>
</html>