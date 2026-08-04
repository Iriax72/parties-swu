<?php
/*
/pages/searchGame.php

Permet un questionement approfondi de la db
*/

// Fonction utilitaire
function error(string $message) {
    // TODO
    echo $message;
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    init_db();
} catch (Throwable $error) {
    error('Impossible d\'initialiser la base de données : ' . $error->getMessage());
}

// Obtenir la liste des leaders depuis /datas.json
$datas = file_get_contents(__DIR__ . '/../datas.json');
$decoded_datas = json_decode($datas, true);
$leader_names = is_array($decoded_datas['leaders'] ?? null)
    ? $decoded_datas['leaders']
    : [];

// Obtenir la liste des decks
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query('SELECT id, name FROM decks');
    $decks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $decks_names = [];
    foreach ($decks as $deck) {
        $decks_names[$deck['id']] = $deck['name'];
    }
} catch (Throwable $error) {
    error('Impossible d\'obtnir les decks depuis la db: ' . $error->getMessage());
}

// Élement DOM
function deck_select(array $decks_names, string $name, string $id): string {
    $ret = "<select name=\"$name\" id=\"$id\" class=\"select\">";
    $ret .= '<option value="all">tous les decks</option>';
    foreach ($decks_names as $d_id => $d_name) {
        $ret .= "<option value=\"$d_id\">$d_name</option>";
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
    <script type="module" src="/js/searchGame.js" defer></script>
</head>
<body>
    <a href="/menu.php" class="btn back-anchor">BACK</a>
    <form class="form">
        <p id="request-p">
            <span class="text">Rechercher les</span>
            <select name="result" id="select1">
                <option value="victory">victoires</option>
                <option value="lose">défaites</option>
                <option value="games">parties</option>
            </select>
            <span class="text">de</span>
            <?= deck_select($decks_names, 'deck1', 'select2'); ?>
            <span>contre</span>
            <?= deck_select($decks_names, 'deck2', 'select3'); ?>
            <span class="text">.</span>
        </p>
        <br>
        <button type="submit" id="submit-btn" class="btn">RECHERCHER</button>
    </form>

    <section id="results" aria-live="polite"></section>
</body>
</html>