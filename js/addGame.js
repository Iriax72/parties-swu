/**
js/addGame.js
*/

import { requestApi, createPopup } from './functions.js';

// Références DOM
const backBtn = document.querySelector('#back-btn');
const form = document.querySelector('form');
const winnerSelect = document.querySelector('#winner');
const loserSelect = document.querySelector('#loser');
const submitBtn = document.querySelector('#submit-btn');

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const winner = winnerSelect.value;
    const loser = loserSelect.value;
    const selectedRadio = document.querySelector('input[name=winningPlayer]:checked');

    if (!selectedRadio) {
        alert('Veuillez choisir le joueur gagnant.');
        return;
    }

    const winningPlayer = selectedRadio.value;
    const params = {
        winner,
        loser,
        winningPlayer,
    };

    const success = await requestApi('add_game', params, () => {
        document.body.append(createPopup(['Partie ajoutée avec succès !']));
    });

    if (!success) {
        submitBtn.disabled = false;
    }
});