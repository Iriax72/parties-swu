/*
/js/searchGame.js
*/
// Imports
import {requestApi} from './functions.js';

// Références DOM
const submitBtn = document.querySelector('#submit-btn');
const historicalRadio = document.querySelector('#historical');
const lastVersionRadio = document.querySelector('#last-version');
const resultSelect = document.querySelector('#resultSelect');
const deck1select = document.querySelector('#deck1select');
const deck2select = document.querySelector('#deck2select');
const results = document.querySelector('#results');

// Charger les datas depuis /datas.json
let datas = null;
const dataPromise = (async () => {
    const response = await fetch('/datas.json');
    if (!response.ok) {
        throw new Error(`Impossible de lire /datas.json: ${response.status}`)
    }
    return response.json()
})().then((loadedDatas) => {
    datas = loadedDatas;
    return loadedDatas;
}).catch((error) => {
    alert('Erreur lors du chargement des données: ' + error.message);
    throw error
});

// Fonction utilitaire
/**
 * @param {string} uri - L'uri du fichier à lire
 */
async function getFileContent (uri) {
    const response = await fetch(uri);
    // Attraper les erreurs
    if (!response.ok) {
        throw new Error(`Impossible de lire ${uri}: ${response.status}`);
    }
    return response.text();
}

/**
 * @param {Array} games - Un tableau des parties à afficher
 * @description Affiche les parties dans le tableau des résultats
 * @returns nothing
 */
function renderResults(games) {
    results.innerHTML = '';

    if (!Array.isArray(games) || games.length === 0) {
        results.textContent = 'Aucune partie trouvée.';
        return;
    }

    const table = document.createElement('table');
    table.classList.add('results-table');

    const thead = document.createElement('thead');
    thead.innerHTML = `
        <tr>
            <th>Deck gagnant</th>
            <th>Deck perdant</th>
            <th>Joueur gagnant</th>
        </tr>
    `;

    const tbody = document.createElement('tbody');
    games.forEach((game) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${game.winnerName ?? 'inconnu'}</td>
            <td>${game.loserName ?? 'inconnu'}</td>
            <td>${game.LeandreWon ? 'Léandre' : 'Lancelot'}</td>
        `;
        tbody.append(row);
    });

    table.append(thead, tbody);
    results.append(table);
}

// EventListeners
submitBtn.addEventListener('click', (event) => {
    event.preventDefault();
    
    if (historicalRadio.checked && lastVersionRadio.checked) {
        alert('Veuillez ne choisir qu\'un mode de recherche');
        return;
    }

    const winning_deck = 
        resultSelect.value === 'victory' ? 1
        : resultSelect.value === 'lose' ? 2
        : null;

    const params = {
        deck1: deck1select.value !== 'all' ? Number(deck1select.value) : null,
        deck2: deck2select.value !== 'all' ? Number(deck2select.value) : null,
        winning_deck: winning_deck,
        last_version_vnly: lastVersionRadio.checked ? 1 : 0
    };

    requestApi('get_games', params, (response) => {
        const games = Array.isArray(response.data) ? response.data : [];
        renderResults(games);
    });
})