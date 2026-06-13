<?php
/*
index.php
Cette page ne renvoie pas d'HTML sauf du texte d'erreur, mais sert de routeur
*/

require_once __DIR__ . '/config.php';
try {
    init_db();
} catch (Throwable $error) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo '<div class="error">' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    exit;
}

header('Location: ./menu.php');
exit;