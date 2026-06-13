/*
js/menu.js
S'occupe de la redirection depuis les btns
*/

// Références DOM
const addGameBtn = document.querySelector("#addGameBtn");
const seeDatasBtn = document.querySelector("#seeDatasBtn");

// EventsListeners des btns
addGameBtn.addEventListener('click', () => {
    window.location.assign('/pages/addGame.php');
});

seeDatasBtn.addEventListener('click', () => {
    window.location.assign('/pages/seeDatas.php');
})