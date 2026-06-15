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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de partie SWU</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/searchGame.css">
    <script src="/js/searchGame.js"></script>
</head>
<body>
    TODO: Mettre une page ici (En attendant ça marche)
</body>
</html>