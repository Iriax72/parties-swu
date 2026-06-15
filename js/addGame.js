/**
js/addGame.js
*/

// Rérénces DOM
const backBtn = document.querySelector("#back-btn");

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});

// ahouter une vérif des entrées lors de la soumission du form (TODO)