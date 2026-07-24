/*
/js/searchGame.js
*/
// Imports
import {requestApi} from './functions.js';

// Références DOM
const backBtn = document.querySelector('#back-btn');
const submitBtn = document.querySelector('#submit-btn');
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
 * @param {string} action - L'action à requeter auprès de l'api
 * @param {Object} params - Les parametres à passer à l'api
 * @param {function} callback - Un callback à executer avec la réponse api
 * @returns {boolean} false en cas d'erreur, true en cas de réussite
 */
/*
function requestApi (action, params = {}, callback = (data) => { }) {
    if (typeof params === 'function') {
        callback = params;
        params = {};
    }
    let uri = `/api.php?action=${encodeURIComponent(action)}`;
    Object.keys(params).forEach(key => {
        uri += `&${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`;
    });

    return fetch(uri, { method: 'GET' })
        .then(async response => {
            const rawText = await response.text();

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${rawText || response.statusText}`);
            }

            try {
                return JSON.parse(rawText);
            } catch (error) {
                throw new Error(`Réponse JSON invalide: ${rawText}`);
            }
        })
        .then(data => {
            if (data && data.success) {
                callback(data);
                return true;
            }

            const error = data?.error ?? "L'api n'a pas spécifié l'erreur";
            alert('Erreur lors de la requete: ' + error);
            return false;
        })
        .catch(error => {
            console.error(error);
            alert('Erreur lors de la requete: ' + (error.message || String(error)));
            return false;
        });
}
*/
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

function createBox(elements) {
    const box = document.createElement('div');
    box.classList.add('box');
    elements.forEach(element => {
        const span = document.createElement('span');
        span.textContent = String(element);
        box.append(span);
    });
    return box;
}

function renderResults(games) {
    const leaderNames = datas.leaders;
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
            <th>Leader gagnant</th>
            <th>Leader perdant</th>
            <th>Joueur gagnant</th>
        </tr>
    `;

    const tbody = document.createElement('tbody');
    games.forEach((game) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${game.winner ? leaderNames[game.winner] : 'inconnu'}</td>
            <td>${game.loser ? leaderNames[game.loser] : 'inconnu'}</td>
            <td>${game.LeandreWon ? 'Léandre' : 'Lancelot'}</td>
        `;
        tbody.append(row);
    });

    table.append(thead, tbody);
    results.append(table);
}

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});

submitBtn.addEventListener('click', (event) => {
    event.preventDefault();
    //const selectedRadio = document.querySelector('input[name="winningLeader"]:checked');
    //const winningLeader = selectedRadio ? selectedRadio.value : 'nobodyWon';
    const winningLeader = select1.value === 'victory' ? 'l1won'
    : select1.value === 'lose' ? 'l2won'
    : 'not_specified';

    const params = {};
    if (select2.value !== 'all') {
        params.leader1 = select2.value;
    }
    if (select3.value !== 'all') {
        params.leader2 = select3.value;
    }
    if (winningLeader !== 'not_specified') {
        params.winningLeader = winningLeader;
    }
    requestApi('get_games', params, (response) => {
        const games = Array.isArray(response.data) ? response.data : [];
        renderResults(games);
    });
})