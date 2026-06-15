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

//EventListener
backBtn.addEventListener('click', () => {
    window.location.assign("/menu.php")
});

leadersWinrateBtn.addEventListener('click', () => {
    const popup = createPopup(['Classement des leaders par winrate:', 'Chargement des données...']);
    document.body.append(popup);
    fetch('/api.php?action=get_leaders_winrate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(JSON.stringify(data, null, 2));
    });
});

playersWinrateBtn.addEventListener('click', () => {
    const popup = createPopup(['Winrate des joueurs:', 'Chargement des données...'])
    document.body.append(popup);
    fetch('/api.php?action=get_players_winrate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(JSON.stringify(data, null, 2));
    });
});

searchGamesBtn.addEventListener('click', () => {
    //rediriger vers une page de requetes
});