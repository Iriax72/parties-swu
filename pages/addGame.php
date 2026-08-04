<?php
/*
pages/addGame.php
Permet d'ajouter des parties à la db
*/

// Fonctions utilitaires
function error(string $message) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo '<div class="error">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}

// Vérifie que la db soit correctement initialisée pour le cas où cette page ait directement été appelée sans passer par l'index
require_once __DIR__ . '/../config.php';
try {
    init_db();
} catch (Throwable $error) {
    error($error->getMessage());
}

// Obtenir la liste des leaders depuis le JSON du projet
$datas = file_get_contents(__DIR__ . '/../datas.json');
$decoded_datas = json_decode($datas, false);
$leader_names = $decoded_datas->leaders;

// Obtenir la liste des decks
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query('
        SELECT decks.id AS deck_id, decks.name AS deck_name, leaders.name AS leaderName, baseColor.colorName AS baseColorName, decks.version AS version
        FROM decks
        JOIN leaders ON decks.leaderId = leaders.id
        JOIN baseColor ON decks.baseColorId = baseColor.id
    ');
    $decks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $error ) {
    error('Impossible d\'obtenir les donées de la db: ' . $error->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parties SWU, Ajouter une partie</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/addGame.css">
    <script type="module" src="/js/addGame.js"></script>
</head>
<body>
    <a href="/menu.php" class="btn back-anchor">BACK</a>
    <form class="form">
        <select name="winner" id="winner" class="select">
            <?php
            foreach ($decks as $deck) {
                echo "<option value=\"{$deck['deck_id']}\">{$deck['leaderName']} {$deck['baseColorName']} ({$deck['deck_name']})</option>";
            }
            ?>
        </select>
        <span>contre</span>
        <select name="loser" id="loser" class="select">
            <?php
            foreach ($decks as $deck) {
                echo "<option value=\"{$deck['deck_id']}\">{$deck['leaderName']} {$deck['baseColorName']} ({$deck['deck_name']})</option>";
            }
            ?>
        </select>
        <p>Gagnant:</p>
        <input type="radio" name="winningPlayer" id="Léandre" value="Léandre">
        <label for="Leandre" class="label">Léandre</label>
        <input type="radio" name="winningPlayer" id="Lancelot" value="Lancelot">
        <label for="Lancelot" class="label">Lancelot</label>
        <button type="submit" id="submit-btn" class="btn">Ajouter la partie</button>
    </form>
</body>
</html>