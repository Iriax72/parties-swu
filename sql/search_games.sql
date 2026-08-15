DELIMITER $$
CREATE OR REPLACE PROCEDURE search_games(
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
        );
END$$
DELIMITER ;