<?php
/*
index.php
Cette page ne renvoie pas d'HTML sauf du texte d'erreur, mais sert de routeur
*/

require_once '/config.php';
try {
    init_db();
} catch (Throwable $error) {
    echo '<link rel="stylesheet" href="/css/main.css">';
    echo "<div class=\"error\">$error</div>";
    exit;
}

header('Location: ./menu.php');
exit;