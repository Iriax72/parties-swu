/*
/js/seeDatas
*/

// Références DOM
const backBtn = document.querySelector("#back-btn");
const leadersWinrateBtn = document.querySelector('#leaders-winrate-btn');
const playersWinrateBtn = document.querySelector('#players-winrate-btn');
const searchGamesBtn = document.querySelector('#search-games-btn');

// Fonctions utilitaires
function createPopup (content) {
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
 * @param {string} url - L'url à qui faire la requête
 * @param {function} callback - Un callback à executer avec les données (dans la variable data)
 */
function requestApi (url, callback = ()=>{}) {
    fetch(url, {
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
    .catch (error => {
        alert('Erreur lors de la requete: ' + error.message);
        return false;
    });
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
    requestApi('/api.php?action=get_leaders_winrate', (data) => {
        waitingText.remove();
        for (let i = 0 ; i < data.winrates.length ; i++) {
            if (data.winrates[i] === -1) continue;
            const box = createBox([
                'leader ',
                String(i + 1),
                ' : ',
                String(Math.round(data.winrates[i] * 100)),
                '%'
            ]);
            popup.append(box);
        }
    });
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