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
        loser INT NOT NULL,
        FOREIGN KEY (loser) REFERENCES leaders(id),
        LeandreWon BOOL NOT NULL
    );');

    // Remplir les tables

    $leaders = $pdo->query('SELECT COUNT(*) AS total FROM leaders')->fetch();
    if ((int) $leaders['total'] === 0) {
        $leader_names = [
            1 => 'Directeur Krennic',
            2 => 'Iden Versio',
            3 => 'Chewbacca',
            4 => 'Chirrut Îmwe',
            5 => 'Luke Skywalker',
            6 => 'Empeureur Palpatine',
            7 => 'Grand Moff Tarkin',
            8 => 'Hera Syndulla',
            9 => 'Leia Organa',
            10 => 'Dark Vador',
            11 => 'Grand Inquisiteur',
            12 => 'IG-88',
            13 => 'Cassian Andor',
            14 => 'Sabine Wren',
            15 => 'Boba Fett',
            16 => 'Grand Amiral Thrawn',
            17 => 'Han Solo',
            18 => 'Jyn Erso'
        ];
        foreach ($leader_names as $id => $name) {
            $stmt = $pdo->prepare('INSERT INTO leaders (id, name) VALUES (:id, :name)');
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
            ]);
        }
    }
}