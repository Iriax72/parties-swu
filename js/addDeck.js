/**
/js/addDeck.js
Le js de la page addDeck.php
*/

// Imports
import { requestApi, createPopup } from "./functions.js";

// Références DOM
const backBtn = document.querySelector('#back-btn');
const form = document.querySelector('form');
const cardArea = document.querySelector('#cards-area');
const addCardBtn = document.querySelector('#add-card-btn');
const addDeckBtn = document.querySelector('#add-deck-btn');

// Event Listerners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
})

addCardBtn.addEventListener('click', () => {
    requestApi('get_cards', (data) => {
        const cards = Array.isArray(data.cards) ? data.cards : [];

        const cardSelect = document.createElement('select');
        cardSelect.classList.add('select');
        cardSelect.setAttribute('name', 'card[]');

        cards.forEach((card) => {
            const option = document.createElement('option');
            option.value = card.id;
            option.textContent = card.name;
            cardSelect.append(option);
        });

        const quantitySelect = document.createElement('select');
        quantitySelect.classList.add('quantity-select');
        quantitySelect.setAttribute('name', 'quantity[]');

        [1, 2, 3].forEach((quantity) => {
            const option = document.createElement('option');
            option.value = quantity;
            option.textContent = quantity;
            quantitySelect.append(option);
        });

        const entryWrapper = document.createElement('div');
        entryWrapper.classList.add('card-entry');
        entryWrapper.append(cardSelect, quantitySelect);

        cardArea.append(entryWrapper);
    });
});

addDeckBtn.addEventListener('click', () => {
    // TODO
});