/*
/js/menu.js

s'occupe de la redirection via les bouttons
S'occupe de l'affichage des classements par winrates
*/

// Références DOM
const addGameBtn = document.querySelector('#addGameBtn');
const leadersWinrateBtn = document.querySelector('#leaders-winrate-btn');
const playersWinrateBtn = document.querySelector('#players-winrate-btn');
const searchGamesBtn = document.querySelector('#search-games-btn');
const seeDecksBtn = document.querySelector('#see-decks-btn');
const addDeckBtn = document.querySelector('#add-deck-btn');

// Charger les datas depuis /datas.json
let datas = null;
const datasPromise = (async () => {
    const response = await fetch('/datas.json');
    if (!response.ok) {
        throw new Error(`Impossible de lire /datas.json: ${response.status}`);
    }
    return response.json();
})().then((loadedDatas) => {
    datas = loadedDatas;
    return loadedDatas;
})
.catch((error) => {
    alert('Erreur lors du chargement des données: ' + error.message);
    throw error;
});

// Fonctions utilitaires
function createPopup(content) {
    // content can be an array of strings and/or HTML Nodes to add in the popup
    const popup = document.createElement('div');
    popup.classList.add('popup');

    const crossBtn = document.createElement('button');
    crossBtn.innerText = 'X';
    crossBtn.classList.add('btn', 'back-btn');
    crossBtn.addEventListener('click', () => {
        popup.remove();
    });
    popup.append(crossBtn);

    content.forEach((element) => {
        popup.append(element); // TODO : sécuriser ça contre le XSS
        popup.append(document.createElement('br'));
    });

    return popup;
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

/**
 * @param {string} action - L'action a requeter auores de l'api
 * @param {Object} params - Les parametre à envoyer à l'api
 * @param {function} callback - Un callback à executer avec les données (dans la variable data)
 * @returns {boolean} false en cas d'erreur, true dans les autres cas
 */
function requestApi(action, params = {}, callback = (data) => { }) {
    if (typeof params === 'function') {
        callback = params;
        params = {};
    }
    let uri = `/api.php?action=${encodeURIComponent(action)}`;
    Object.keys(params).forEach(key => {
        uri += `&${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`;
    });

    return fetch(uri, { method: 'GET' })
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                callback(data);
                return true;
            } else {
                const error = data.error ?? "L'api n'a pas spécifié l'erreur";
                alert("Erreur lors de la requete: " + error);
                return false;
            }
        })
        .catch(error => {
            alert('Erreur lors de la requete: ' + error.message);
            return false;
        });
}

/**
 * @param {string} uri - L'uri du fichier à lire
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
addGameBtn.addEventListener('click', () => {
    window.location.assign('/pages/addGame.php')
});

addDeckBtn.addEventListener('click', () => {
    window.location.assign('/pages/addDeck.php');
})

leadersWinrateBtn.addEventListener('click', () => {
    const waitingText = document.createElement('p');
    waitingText.innerText = 'Chargement des données...';
    const popup = createPopup(['Classement des leaders par winrate:', waitingText]);
    document.body.append(popup);
    datasPromise.then(() => requestApi('get_leaders_winrate', (data) => {
        waitingText.remove();
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
    }));
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

searchGamesBtn.addEventListener('click', () => {
    window.location.assign('/pages/searchGame.php');
});

seeDecksBtn.addEventListener('click', () => {
    const waitingText = document.createEoement('p');
    waitingText.innerText = 'Chargement des decks...';
    const popup = createPopup(['Decks:', waitingText]);
    document.body.append(popup);
    reauestApi('get_decks', (data) => {
        waitingText.remove();
        json_decode(data).foreach((deck) => {
            popup.append(createBox([`${deck.leader} ${deck.baseColorName} ${deck.version} (${deck.name})`]));
        });
    })
})