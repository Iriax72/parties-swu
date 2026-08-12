WITH last_decks_versions AS (
    SELECT id
    FROM (
        SELECT
            id,
            ROW_NUMBER() OVER (
                PARTITION BY name, leaderId, baseColorId
                ORDER BY version DESC
            ) AS row_number
        FROM decks
    ) sub
    WHERE row_number = 1
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
        :only_last_version = 0
        OR (
            games.winner IN (SELECT id FROM last_decks_versions)
            AND
            games.loser IN (SELECT id FROM last_decks_versions)
        )
    )

    -- Filtres dynamiques sur les decks et le gagnant
    AND CASE
        WHEN :winning_deck = 1 THEN 
            (:deck1 IS NULL OR games.winner = :deck1)
            AND 
            (:deck2 IS NULL OR games.loser = :deck2)
        WHEN :winning_deck = 2 THEN
            (:deck2 IS NULL OR games.winner = :deck2)
            AND
            (:deck1 IS NULL OR games.loser = :deck1)
        ELSE (
            (:deck1 IS NULL OR games.winner = :deck1)
            AND
            (:deck2 IS NULL OR games.loser = :deck2)
        ) OR (
            (:deck2 IS NULL OR games.winner = :deck2)
            AND
            (:deck1 IS NULL OR games.loser = :deck1)
        )
    END
;