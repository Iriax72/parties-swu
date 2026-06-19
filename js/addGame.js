/**
js/addGame.js
*/

import { requestApi, createPopup } from "./functions.js";

// Rérénces DOM
const backBtn = document.querySelector("#back-btn");
const winnerSelect = document.querySelector('#winner');
const loserSelect = document.querySelector('#loser');
const submitBtn = document.querySelector('#submit-btn');

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});

submitBtn.addEventListener('click', () => {
    // Obtenir les donnés du form
    const winner = winnerSelect.value;
    const loser = loserSelect.value;
    const selectedRadio = document.querySelector('input[name=winningPlayer]:checked');
    const winningPlayer = selectedRadio.value;
    const params = {
        winner: winner,
        loser: loser,
        winningPlayer: winningPlayer
    }
    // Envoyer la requête
    requestApi('add_game', params, (data) => {
        createPopup(['Partie ajoutée avec succès!']);
    });
});