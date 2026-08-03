<?php 
/*
pages/seeDeck.php
Permet de voir un deck en détail
*/

function error(string $message) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo '<div class="error">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}

function convertHTML (string $message) {
    return htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Vérifie que la db soit correctement initialisée
require_once __DIR__ . '/../config.php';
try {
    init_db();
} catch (Throwable $error) {
    error('Impossible d\'initialiser la db: ' . $error->getMessage());
}

// Se connecter à la db
try {
    $pdo = get_db_connection();
} catch (Throwable $error) {
    error('Impossible de se connecter à la db: ' . $error->getMessage());
}

// Trouver l'id du deck
if (!isset($_GET['deck_id'])) {
    error('ID du deck non spécifié');
}
$deck_id = $_GET['deck_id'];

// Récupérer les infos du deck
$stmt = $pdo->prepare('
    SELECT decks.name AS deckName, decks.version AS version, decks.lastUpdate AS lastUpdate,
    leaders.name AS leaderName,
    baseColor.colorName AS baseColorName, baseColor.officialName AS baseOfficialName
    FROM decks
    JOIN leaders ON decks.leaderId = leaders.id
    JOIN baseColor ON decks.baseColorId = baseColor.id
    WHERE decks.id = :deck_id;
');
$stmt->execute([':deck_id' => $deck_id]);
$deck = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier qu'un deck a été trouvé
if ($deck === false) {
    error('Deck introuvable');
}

// Récupérer la liste des cartes et leur quantité
$stmt = $pdo->prepare('
    SELECT cartes.id AS cardId, cartes.name AS name, cd.exemplaires AS quantity
    FROM cartes 
    JOIN cartes_dans_decks AS cd ON cartes.id = cd.cardId
    WHERE cd.deckId = :deck_id;
');
$stmt->execute([':deck_id' => $deck_id]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>parties swu - voir le deck <?= $deck['deckName'] ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/seeDeck.css">
</head>
<body>
    <a href="/menu.php" class="btn back-anchor">BACK</a>
    <h1>
        <?= convertHTML($deck['leaderName']) ?>
        <?= convertHTML($deck['baseColorName']) ?>
    </h1>
    <p>
        Nom: <?= convertHTML($deck['deckName']) ?>
    </p>
    <p>
        version <?= convertHTML($deck['version']) ?>
        (Mis à jour le <?= convertHTML($deck['lastUpdate']) ?>)
    </p>
    <h2>Cartes:</h2>
    <ul>
        <?php
        foreach ($cards as $card) {
            echo '<li>';
            echo convertHTML($card['name']);
            echo ' ( x' . convertHTML($card['quantity']) . ')';
            echo '</li>';
        }
        ?>
    </ul>
</body>
</html>