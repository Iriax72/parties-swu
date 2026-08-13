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
    winner_deck.id AS winner_deck_id,
    loser_deck.id AS loser_deck_id
FROM games
LEFT JOIN decks AS winner_deck ON games.winner = winner_deck.id
LEFT JOIN decks AS loser_deck ON games.loser = loser_deck.id
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