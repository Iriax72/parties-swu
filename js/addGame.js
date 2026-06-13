/**
js/addGame.js
*/

// Rérénces DOM
const backBtn = document.querySelector("#back-btn");

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});