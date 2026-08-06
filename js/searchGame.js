/*
/js/searchGame.js
*/
// Imports
import {requestApi} from './functions.js';

// Références DOM
const submitBtn = document.querySelector('#submit-btn');
const historicalRadio = document.querySelector('#historical');
const lastVersionRadio = document.querySelector('#last-version');
const select1 = document.querySelector('#select1');
const select2 = document.querySelector('#select2');
const select3 = document.querySelector('#select3');
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

    const historicalModeActive = historicalRadio.checked ? true : false;

    const winningLeader = select1.value === 'victory' ? 'l1won'
        : select1.value === 'lose' ? 'l2won'
        : null;

    const params = {};
    if (select2.value !== 'all') {
        params.deck1 = select2.value;
    }
    if (select3.value !== 'all') {
        params.deck2 = select3.value;
    }
    if (winningLeader !== null) {
        params.winningLeader = winningLeader;
    }
    params.historicalModeActive = historicalModeActive;
    requestApi('get_games', params, (response) => {
        const games = Array.isArray(response.data) ? response.data : [];
        renderResults(games);
    });
})