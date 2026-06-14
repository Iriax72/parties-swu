/*
/js/seeDatas
*/

// Références DOM
const backBtn = document.querySelector("#back-btn");
const leadersWinrateBtn = document.querySelector('#leaders-winrate-btn');
const playersWinrateBtn = document.querySelector('#players-winrate-btn');
const searchGamesBtn = document.quetySelector('#search-games-btn');

// Fonctions utilitaires
function createPopup (content) {
    // content can be an array of strings and/or HTML Nodes to add in the popup
    const popup = document.createElement('div');
    popup.classList.add('popup');

    const crossBtn = document.createElement('button');
    crossBtn.innerText = 'X';
    crossBtn.classList.add(['btn', 'btn2']);
    crossBtn.addEventListener('click', () => {
        popup.remove();
    });
    popup.append(crossBtn);

    content.forEach((element) => {
        popup.append(element);
    });

    return popup;
}

//EventListener
backBtn.addEventListener('click', () => {
    window.location.assign("/menu.php")
});

leadersWinrateBtn.addEventListener('click', () => {
    const popup = createPopup(['Classement des leaders par winrate:']);
    document.append(popup);
});

playersWinrateBtn.addEventListener('click', () => {
    const popup = createPopup(['Winrate des joueurs:'])
    document.append(popup);
});

searchGamesBtn.addEventListener('click', () => {
    //rediriger vers une page de requetes
});