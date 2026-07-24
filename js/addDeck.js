/**
/js/addDeck.js
Le js de la page addDeck.php
*/

// Imports
import { requestApi, createPopup } from "./functions.js";

// Références DOM
const form = document.querySelector('form');
const cardArea = document.querySelector('#card-area');
const addCardBtn = document.querySelector('#add-card-btn');
const addDeckBtn = document.querySelector('#add-deck-btn');

function createCardInput() {
    const cardInput = document.createElement('input');
    cardInput.classList = 'select';
    cardInput.type = 'select'
    const label = document.createElement('label');
}

// Event Listerners
addCardBtn.addEventListener('click', () => {
    const cardInput = createCardInput();
    cardArea.append(cardInput);
});

addDeckBtn.addEventListener('click', () => {

});