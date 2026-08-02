/*
/js/menu.js

S'occupe de l'affichage des classements par winrates et des decks
*/
// Imports
import { requestApi, getDatas, createPopup, createBox } from './functions.js';

// Références DOM
const addGameBtn = document.querySelector('#addGameBtn');
const leadersWinrateBtn = document.querySelector('#leaders-winrate-btn');
const playersWinrateBtn = document.querySelector('#players-winrate-btn');
const searchGamesBtn = document.querySelector('#search-games-btn');
const seeDecksBtn = document.querySelector('#see-decks-btn');
const addDeckBtn = document.querySelector('#add-deck-btn');

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

// Fonctions utilitaires
/**
 * 
 * @param {String} uri - L'uri de fichier à lire
 * @returns {String} Le contenu du fichier
 */ 
async function getFileContent(uri) {
    const response = await fetch(uri);
    // Attraper les erreurs
    if (!response.ok) {
        throw new Error(`Impossible de lire ${uri}: ${response.status}`);
    }
    return response.text();
}

//EventListeners
leadersWinrateBtn.addEventListener('click', async () => {
    const waitingText = document.createElement('p');
    waitingText.innerText = 'Chargement des données...';
    const popup = createPopup(['Classement des leaders par winrate:', waitingText]);
    document.body.append(popup);

    try {
        await datasPromise;
        if (!datas) {
            throw new Error(datasError ?? 'Les données ne sont pas disponibles');
        }

        waitingText.remove();
        requestApi('get_leaders_winrate', (data) => {
            const leaderNames = datas.leaders;
            const sortedWinrates = data.winrates
                .map((winrate, index) => ({ winrate, index }))
                .filter((item) => item.winrate !== -1)
                .sort((a, b) => b.winrate - a.winrate);
            for (const item of sortedWinrates) {
                const leaderName = leaderNames[String(item.index + 1)] ?? `Leader ${item.index + 1}`;
                const box = createBox([
                    leaderName,
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