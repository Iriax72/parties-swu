<?php
/*
/pages/addDeck.php
Permet d'ajouter des decks à la db
*/

// Vérifie que la db soit correctement initialisée
require_once __DIR__ . '/../config.php';
try {
    init_db();
} catch (throwable $error) {
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
    <title>Parties swu, ajouter un deck</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/addDeck.css">
    <script type="module" src="/js/addDeck.js" defer></script>
</head>
<body>
    <button class="btn back-btn" type="button" id="back-btn">BACK</button>
    <form class="form">
        <div id="cards-area">Il n'y a pas encore de carte dans le deck</div>
        <button id="add-card-btn" class="btn" type="button">Ajouter une carte</button>
        <button id="add-deck-btn" class="btn" type="submit">Ajouter le deck</button>
    </form>
</body>
</html>