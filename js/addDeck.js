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
// const addDeckBtn = document.querySelector('#add-deck-btn');

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
        quantitySelect.classList.add('select');
        quantitySelect.setAttribute('name', 'quantity[]');

        [1, 2, 3].forEach((quantity) => {
            const option = document.createElement('option');
            option.value = quantity;
            option.textContent = quantity;
            quantitySelect.append(option);
        });

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'X';
        removeBtn.classList.add('btn');
        removeBtn.addEventListener('click', () => {
            entryWrapper.remove();
        });

        const entryWrapper = document.createElement('div');
        entryWrapper.classList.add('entry-wrapper');
        entryWrapper.append(cardSelect, quantitySelect, removeBtn);

        cardArea.append(entryWrapper);
    });
});

form.addEventListener('submit', (event) => {
    event.preventDefault();

    // Définir le deck
    const currentDeck = {}
    const entryWrappers = cardArea.querySelectorAll('.entry-wrapper');
    entryWrappers.forEach((wrapper) => {
        const cardId = wrapper.querySelector('select[name="card[]"]').value;
        const exemplaires = wrapper.querySelector('select[name="quantity[]"]').value;
        currentDeck[cardId] = Number(exemplaires);
    });
    
    // Ajouter le deck à l'api
    requestApi(
        'add_deck',
        {deck: currentDeck}, 
        (data) => {
            // Indiquer une validation à l'user
            createPopup('Le deck à été ajouté avec succès !');
        }
    )
});