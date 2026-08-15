<?php
/*
config.php
configure la db
*/

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_PORT', getenv('DB_PORT'));
define('DB_USER', getenv('DB_USERNAME'));
define('DB_PASS', getenv('DB_PASSWORD'));
define('DB_CHARSET', 'utf8mb4');

function get_db_connection() :PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s;',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

function init_db() :void {
    $pdo = get_db_connection();

    // Créer les tables

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS leaders (
        id TINYINT UNSIGNED PRIMARY KEY,
        name VARCHAR(25) NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS baseColor(
        id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        colorName VARCHAR(5) NOT NULL,
        officialName VARCHAR(12) NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS cartes (
        id INT UNSIGNED PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS decks (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) DEFAULT \'\',
        leaderId TINYINT UNSIGNED NOT NULL,
        baseColorId TINYINT UNSIGNED NOT NULL,
        version INT UNSIGNED NOT NULL DEFAULT 1,
        lastUpdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (leaderId) REFERENCES leaders(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        FOREIGN KEY (baseColorId) REFERENCES baseColor(id) ON DELETE RESTRICT ON UPDATE RESTRICT
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS games (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        winner INT UNSIGNED NOT NULL,
        loser INT UNSIGNED NOT NULL,
        LeandreWon BOOL NOT NULL,
        FOREIGN KEY (winner) REFERENCES decks(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        FOREIGN KEY (loser) REFERENCES decks(id) ON DELETE RESTRICT ON UPDATE RESTRICT
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS cartes_dans_decks (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        cardId INT UNSIGNED NOT NULL,
        deckId INT UNSIGNED NOT NULL,
        exemplaires TINYINT UNSIGNED NOT NULL DEFAULT 1,
        FOREIGN KEY (cardId) REFERENCES cartes(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        FOREIGN KEY (deckId) REFERENCES decks(id) ON DELETE RESTRICT ON UPDATE RESTRICT
    );');

    $content = file_get_contents(__DIR__ . '/sql/search_games.sql');
    $pdo->exec('DROP PROCEDURE IF EXISTS search_games;');
    $pdo->exec($content);

    // Remplir les tables si elles sont vides
    $datas = file_get_contents(__DIR__ . '/datas.json');
    $datas = json_decode($datas);

    $total_leaders = $pdo->query('SELECT COUNT(*) AS total FROM leaders')->fetch();
    if ((int) $total_leaders['total'] === 0) {
        $leader_names = $datas->leaders;
        foreach ($leader_names as $id => $name) {
            $stmt = $pdo->prepare('INSERT INTO leaders (id, name) VALUES (:id, :name)');
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
            ]);
        }
    }

    $totalBase = $pdo->query('SELECT COUNT(*) AS total FROM baseColor')->fetch();
    if ((int) $totalBase['total'] === 0) {
        $bases = $datas->bases;
        foreach ($bases as $colorName => $officialName) {
            $stmt = $pdo->prepare('INSERT INTO baseColor (colorName, officialName) VALUES (:colorName, :officialName)');
            $stmt->execute([
                ':colorName' => $colorName,
                ':officialName' => $officialName
            ]);
        }
    }

    try {
        $totalCards = $pdo->query('SELECT COUNT(*) AS total FROM cartes')->fetch();
        if ((int) $totalCards['total'] === 0) {
            $cards = $datas->cartes;
            foreach ($cards as $id => $name) {
                $stmt = $pdo->prepare('INSERT INTO cartes (id, name) VALUES (:id, :name)');
                $stmt->execute([
                    ':id' => $id,
                    ':name' => $name
                ]);
            }
        }
    } catch (Throwable $e) {
        echo '<link rel="stylesheet" href="/css/main.css">';
        echo '<div class="error">' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</div>';
    }
}