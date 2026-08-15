/*
/js/menu.js

S'occupe de l'affichage des classements par winrates et des decks
*/
// Imports
import { requestApi, getDatas, createPopup, createBox, getFileContent} from './functions.js';

// Références DOM
const addGameBtn = document.querySelector('#addGameBtn');
const decksWinrateBtn = document.querySelector('#decks-winrate-btn');
const playersWinrateBtn = document.querySelector('#players-winrate-btn');
const searchGamesBtn = document.querySelector('#search-games-btn');
const seeDecksBtn = document.querySelector('#see-decks-btn');
const addDeckBtn = document.querySelector('#add-deck-btn');

// Obtenir les noms des decks depuis l'api
let decksNames = {};
requestApi('get_decks', (data) => {
    const decks = data.decks ?? [];
    for (const deck of decks) {
        decksNames[String(deck.id)] = `${deck.leaderName} ${deck.baseColorName} ${deck.version} (${deck.name})`.trim();
    }
});

// Charger les datas depuis /datas.json
let datas = null;
let datasError = null;
const datasPromise = getDatas()
    .then((loadedDatas) => {
        datas = loadedDatas;
    })
    .catch((error) => {
        datasError = error instanceof Error ? error.message : String(error);
        console.error('Erreur lors du chargement des données:', error);
    });

//EventListeners
decksWinrateBtn.addEventListener('click', async () => {
    const waitingText = document.createElement('p');
    waitingText.innerText = 'Chargement des données...';
    const popup = createPopup(['Classement des decks par winrate:', waitingText]);
    document.body.append(popup);

    try {
        await datasPromise;
        if (!datas) {
            throw new Error(datasError ?? 'Les données ne sont pas disponibles');
        }

        waitingText.remove();
        requestApi('get_decks_winrate', (data) => {
            const deckNames = decksNames;
            const sortedWinrates = Object.entries(data.winrates ?? {})
                .map(([deckId, winrate]) => ({ deckId: Number(deckId), winrate: Number(winrate) }))
                .filter((item) => item.winrate !== -1)
                .sort((a, b) => b.winrate - a.winrate);
            for (const item of sortedWinrates) {
                const deckName = deckNames[String(item.deckId)] ?? `Deck ${item.deckId}`;
                const box = createBox([
                    deckName,
                    ' : ',
                    String(Math.round(item.winrate * 100)),
                    '%'
                ]);
                popup.append(box);
            }
        });
    } catch (error) {
        waitingText.remove();
        popup.append(createBox([error instanceof Error ? error.message : String(error)]));
    }
});

playersWinrateBtn.addEventListener('click', () => {
    const waitingText = document.createElement('p');
    waitingText.innerText = 'Chargement des données...';
    const popup = createPopup(['Winrate des joueurs:', waitingText]);
    document.body.append(popup);
    requestApi('get_players_winrate', (data) => {
        waitingText.remove();
        popup.append(createBox(['Léandre : ', String(Math.round(data.winrateLeandre * 100)), '%']));
        popup.append(createBox(['Lancelot : ', String(Math.round(data.winrateLancelot * 100)), '%']));
    });
});

seeDecksBtn.addEventListener('click', () => {
    const waitingText = document.createElement('p');
    waitingText.innerText = 'Chargement des decks...';
    const popup = createPopup(['Decks:', waitingText]);
    document.body.append(popup);
    requestApi('get_decks', (data) => {
        waitingText.remove();
        (data.decks ?? []).forEach((deck) => {
            popup.append(createBox(
                [`${deck.leaderName} ${deck.baseColorName} ${deck.version} (${deck.name})`.trim()],
                '/pages/seeDeck.php?deck_id=' + encodeURIComponent(deck.id)
            ));
        });
    })
})