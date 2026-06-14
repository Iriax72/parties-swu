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
    CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        winner TINYINT NOT NULL,
        FOREIGN KEY (winner) REFERENCES leaders(id),
        loser TINYINT NOT NULL,
        FOREIGN KEY (loser) REFERENCES leaders(id),
        LeandreWon BOOL NOT NULL
    );');

    // Remplir les tables si elles sont vides

    $leaders = $pdo->query('SELECT COUNT(*) AS total FROM leaders')->fetch();
    if ((int) $leaders['total'] === 0) {
        // Lire la liste des leaders depuis le JSON du projet
        $datas = file_get_contents(__DIR__ . '/datas.json');
        $decoded_datas = json_decode($datas, false);
        $leader_names = $decoded_datas->leaders;
        foreach ($leader_names as $id => $name) {
            $stmt = $pdo->prepare('INSERT INTO leaders (id, name) VALUES (:id, :name)');
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
            ]);
        }
    }
}