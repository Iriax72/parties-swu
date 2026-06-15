/*
/js/seeDatas
*/

// Références DOM
const backBtn = document.querySelector("#back-btn");
const leadersWinrateBtn = document.querySelector('#leaders-winrate-btn');
const playersWinrateBtn = document.querySelector('#players-winrate-btn');
const searchGamesBtn = document.querySelector('#search-games-btn');

// Charger les datas depuis /datas.json
let datas = null;
const datasPromise = (async () => {
    const response = await fetch('/datas.json');
    if (!response.ok) {
        throw new Error(`Impossible de lire /datas.json: ${response.status}`);
    }
    return response.json();
}).then((loadedDatas) => {
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
    crossBtn.classList.add('btn', 'btn2');
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
        box.textContent += element; // TODO : sécuriser contre le XSS
    });
    return box;
}

/**
 * @param {string} uri - L'uri à qui faire la requête
 * @param {function} callback - Un callback à executer avec les données (dans la variable data)
 * @returns {boolean} false en cas d'erreur, true dans les autres cas
 */
function requestApi(uri, callback = () => { }) {
    fetch(uri, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
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
 * @param {function} callback - Un callback à executer après la lecture
 */
async function getFileContent(uri) {
    const response = await fetch(uri);

    if (!response.ok) {
        throw new Error(`Impossible de lire ${uri}: ${response.status}`);
    }

    return response.text();
}

//EventListener
backBtn.addEventListener('click', () => {
    window.location.assign("/menu.php")
});

leadersWinrateBtn.addEventListener('click', () => {
    const waitingText = document.createElement('p');
    waitingText.innerText = 'Chargement des données...';
    const popup = createPopup(['Classement des leaders par winrate:', waitingText]);
    document.body.append(popup);
    datasPromise.then(() => requestApi('/api.php?action=get_leaders_winrate', (data) => {
        waitingText.remove();
        const leaderNames = datas.leaders;
        const sortedWinrates = data.winrates
            .map((winrate, index) => ({ winrate, index }))
            .filter((item) => item.winrate !== -1)
            .toSorted((a, b) => b.winrate - a.winrate);
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
    const popup = createPopup(['Winrate des joueurs:', waitingText])
    document.body.append(popup);
    requestApi('/api.php?action=get_players_winrate', (data) => {
        waitingText.remove();
        popup.append(createBox(['Léandre : ', String(Math.round(data.winrateLeandre * 100)), '%']));
        popup.append(createBox(['Lancelot : ', String(Math.round(data.winrateLancelot * 100)), '%']));
    });
});

searchGamesBtn.addEventListener('click', () => {
    //rediriger vers une page de requetes
});