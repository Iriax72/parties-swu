-- Créer les tables

CREATE TABLE IF NOT EXISTS leaders (
    id TINYINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS baseColor (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colorName VARCHAR (255) NOT NULL,
    officialName VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS cartes (
    id INT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS decks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL DEFAULT '',
    leaderId TINYINT UNSIGNED NOT NULL,
    baseColorId TINYINT UNSIGNED NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    lastUpdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leaderId) REFERENCES leaders(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    FOREIGN KEY (baseColorId) REFERENCES baseColor(id) ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE IF NOT EXISTS games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    winner INT UNSIGNED NOT NULL,
    loser INT UNSIGNED NOT NULL,
    LeandreWon BOOL NOT NULL,
    FOREIGN KEY (winner) REFERENCES decks(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    FOREIGN KEY (loser) REFERENCES decks(id) ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE IF NOT EXISTS cartes_dans_decks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cardId INT UNSIGNED NOT NULL,
    deckId INT UNSIGNED NOT NULL,
    exemplaires TINYINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (cardId) REFERENCES cartes(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    FOREIGN KEY (deckId) REFERENCES decks(id) ON DELETE RESTRICT ON UPDATE RESTRICT
);


-- Créer les vues

DROP VIEW IF EXISTS decks_winrates;

CREATE VIEW decks_winrates AS 
WITH decks_games AS (
    SELECT
        winning_deck.id AS deck_id,
        winning_deck.name AS deck_name,
        1 AS isWin
    FROM games g
    JOIN decks winning_deck ON g.winner = winning_deck.id

    UNION ALL
    SELECT
        losing_deck.id AS deck_id,
        losing_deck.name AS deck_name,
        0 AS isWin
    FROM games g
    JOIN decks losing_deck ON g.loser = losing_deck.id
)
SELECT
    deck_id,
    deck_name,
    COUNT(*) AS total_games,
    COALESCE(SUM(isWin), 0) AS total_wins,
    COUNT(*) - COALESCE(SUM(isWin), 0) AS total_losses,
    CASE
        WHEN COUNT(*) > 0 THEN ROUND(COALESCE(SUM(isWin), 0) / COUNT(*), 4)
        ELSE 0
    END AS winrate
FROM decks_games
GROUP BY deck_id, deck_name
ORDER BY winrate DESC;


DROP VIEW IF EXISTS players_winrates;

CREATE VIEW players_winrates AS
SELECT
    COUNT(*) AS nb_games,
    COALESCE(SUM(CASE WHEN LeandreWon = 1 THEN 1 ELSE 0 END), 0) AS LeandreVictory,
    CASE
        WHEN COUNT(*) > 0 THEN ROUND(COALESCE(SUM(CASE WHEN LeandreWon = 1 THEN 1 ELSE 0 END), 0) / COUNT(*), 2)
        ELSE 0
    END AS winrateLeandre,
    CASE
        WHEN COUNT(*) > 0 THEN ROUND((COUNT(*) - COALESCE(SUM(CASE WHEN LeandreWon = 1 THEN 1 ELSE 0 END), 0)) / COUNT(*), 2)
        ELSE 0
    END AS winrateLancelot
FROM games;

-- Créer les procédures

DROP PROCEDURE IF EXISTS search_games;

CREATE PROCEDURE search_games (
    IN p_deck1 INT UNSIGNED,
    IN p_deck2 INT UNSIGNED,
    IN p_winning_deck TINYINT UNSIGNED,
    IN p_last_version_only TINYINT(1),
    IN p_winning_player VARCHAR(10)
)
BEGIN
    WITH last_decks_versions AS (
        SELECT id
        FROM (
            SELECT
                id,
                ROW_NUMBER() OVER (
                    PARTITION BY name, leaderId, baseColorId
                    ORDER BY version DESC
                ) AS rn
            FROM decks
        ) ranked
        WHERE rn = 1
    )
    SELECT
        g.*,
        CONCAT(winning_leader.name, ' ', winning_base.colorName, ' (', winner_deck.version, ') ', winner_deck.name) AS winner_slug,
        CONCAT(losing_leader.name, ' ', losing_base.colorName, ' (', loser_deck.version, ') ', loser_deck.name) AS loser_slug
    FROM games g
    LEFT JOIN decks AS winner_deck ON g.winner = winner_deck.id
    LEFT JOIN decks AS loser_deck ON g.loser = loser_deck.id
    LEFT JOIN leaders AS winning_leader ON winner_deck.leaderId = winning_leader.id
    LEFT JOIN leaders AS losing_leader ON loser_deck.leaderId = losing_leader.id
    LEFT JOIN baseColor AS winning_base ON winner_deck.baseColorId = winning_base.id
    LEFT JOIN baseColor AS losing_base ON loser_deck.baseColorId = losing_base.id
    WHERE
        (
            p_last_version_only = 0
            OR (
                g.winner IN (SELECT id FROM last_decks_versions)
                AND g.loser IN (SELECT id FROM last_decks_versions)
            )
        )
        AND (
            CASE
                WHEN p_winning_deck = 1 THEN
                    (p_deck1 IS NULL OR g.winner = p_deck1)
                    AND (p_deck2 IS NULL OR g.loser = p_deck2)
                WHEN p_winning_deck = 2 THEN
                    (p_deck2 IS NULL OR g.winner = p_deck2)
                    AND (p_deck1 IS NULL OR g.loser = p_deck1)
                ELSE (
                    (p_deck1 IS NULL OR g.winner = p_deck1)
                    AND (p_deck2 IS NULL OR g.loser = p_deck2)
                ) OR (
                    (p_deck2 IS NULL OR g.winner = p_deck2)
                    AND (p_deck1 IS NULL OR g.loser = p_deck1)
                )
            END
        )
        AND (
            p_winning_player IS NULL
            OR (
                (p_winning_player = 'Léandre' AND g.LeandreWon = 1)
                OR (p_winning_player = 'Lancelot' AND g.LeandreWon = 0)
            )
        )
    ;
END;