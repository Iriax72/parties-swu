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
    ) sub
    WHERE rn = 1
)

SELECT
    games.*,
    CONCAT(winning_leader.name, ' ', winning_base.colorName, ' (', winner_deck.version, ') ', winner_deck.name) AS winner_slug,
    CONCAT(losing_leader.name, ' ', losing_base.colorName, ' (', loser_deck.version, ') ', loser_deck.name) AS loser_slug
FROM games
LEFT JOIN decks AS winner_deck ON games.winner = winner_deck.id
LEFT JOIN decks AS loser_deck ON games.loser = loser_deck.id
LEFT JOIN leaders AS winning_leader ON winner_deck.leaderId = winning_leader.id
LEFT JOIN leaders AS losing_leader ON loser_deck.leaderId = losing_leader.id
LEFT JOIN baseColor AS winning_base ON winner_deck.baseColorId = winning_base.id
LEFT JOIN baseColor AS losing_base ON loser_deck.baseColorId = losing_base.id
WHERE
    -- Filtre optionnel last_version_only
    (
        ? = 0
        OR (
            games.winner IN (SELECT id FROM last_decks_versions)
            AND
            games.loser IN (SELECT id FROM last_decks_versions)
        )
    )

    -- Filtres dynamiques sur les decks et le gagnant
    AND CASE
        WHEN ? = 1 THEN 
            (? IS NULL OR games.winner = ?)
            AND 
            (? IS NULL OR games.loser = ?)
        WHEN ? = 2 THEN
            (? IS NULL OR games.winner = ?)
            AND
            (? IS NULL OR games.loser = ?)
        ELSE (
            (? IS NULL OR games.winner = ?)
            AND
            (? IS NULL OR games.loser = ?)
        ) OR (
            (? IS NULL OR games.winner = ?)
            AND
            (? IS NULL OR games.loser = ?)
        )
    END
;