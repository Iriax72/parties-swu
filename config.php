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
        id TINYINT PRIMARY KEY,
        name VARCHAR(25) NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS baseColor(
        id TINYINT AUTO_INCREMENT PRIMARY KEY,
        colorName VARCHAR(5) NOT NULL,
        officialName VARCHAR(12) NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        winner TINYINT NOT NULL,
        FOREIGN KEY (winner) REFERENCES leaders(id),
        loser TINYINT NOT NULL,
        FOREIGN KEY (loser) REFERENCES leaders(id),
        LeandreWon BOOL NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS cartes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS decks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) DEFAULT \'\',
        leaderId TINYINT NOT NULL,
        baseColorId TINYINT NOT NULL,
        version VARCHAR(8) NOT NULL DEFAULT \'1\',
        lastUpdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (leaderId) REFERENCES leaders(id),
        FOREIGN KEY (baseColorId) REFERENCES baseColor(id)
    );');

    $pdo->exec('
    CREATE TABLE IF NOT EXISTS cartes_dans_decks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cardId INT NOT NULL,
        deckId INT NOT NULL,
        FOREIGN KEY (cardId) REFERENCES cartes(id),
        FOREIGN KEY (deckId) REFERENCES decks(id)
    );');

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
    if ((int) $totalBase === 0) {
        $bases = $datas->bases;
        $baseId = 1;
        foreach ($bases as $colorName => $officialName) {
            $stmt = $pdo->prepare('INSERT INTO baseColor (id, colorName, officialName) VALUES (:id, :colorName, :officialName)');
            $stmt->execute([
                ':id' => $baseId,
                ':colorName' => $colorName,
                ':officialName' => $officialName
            ]);
            $baseId++;
        }
    }

    $totalCards = $pdo->query('SELECT COUNT(*) AS total FROM cartes')->fetch();
    if ((int) $totalCards === 0) {
        $cards = $datas->cartes;
        foreach ($cards as $id => $name) {
            $stmt = $pdo->prepare('INSERT INTO cartes (id, name) VALUES (:id, :name)');
            $stmt->execute([
                ':id' => $id,
                ':name' => $name
            ]);
        }
    }

    // Test
    $pdo->exec('DELETE FROM decks;');
    $pdo->exec('INSERT INTO decks (name, leaderId, baseColorId, version) VALUES (\'Test\', 6, 2, \'1.02\')');
    $pdo->exec('INSERT INTO decks (name, leaderId, baseColorId, version) VALUES (\'test2\', 2, 1, \'2.1\')');
}