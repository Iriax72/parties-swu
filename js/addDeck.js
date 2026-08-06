/**
/js/addDeck.js
Le js de la page addDeck.php
*/

// Imports
import { requestApi, getDatas, createPopup } from "./functions.js";

// Références DOM
const form = document.querySelector('form');
const cardArea = document.querySelector('#cards-area');
const addCardBtn = document.querySelector('#add-card-btn');

// Event Listerners
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

        const entryWrapper = document.createElement('div');
        entryWrapper.classList.add('entry-wrapper');
        entryWrapper.append(cardSelect, quantitySelect, removeBtn);

        removeBtn.addEventListener('click', () => {
            entryWrapper.remove();
        });

        cardArea.append(entryWrapper);
    });
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const name = form.querySelector('#name-input').value.trim();
    const versionInput = form.querySelector('#version-input').value.trim();
    const leaderName = form.querySelector('#leader-input').value.trim();
    const baseInput = form.querySelector('#base-input').value.trim();

    const version = versionInput === '' ? 1 : Number(versionInput);
    if (!Number.isInteger(version) || version < 1) {
        document.body.append(createPopup(['La version doit être un entier positif.']));
        return;
    }

    if (!name) {
        document.body.append(createPopup(['Le nom du deck est obligatoire.']));
        return;
    }

    if (!leaderName) {
        document.body.append(createPopup(['Le nom du leader est obligatoire.']));
        return;
    }

    let datas;
    try {
        datas = await getDatas();
    } catch (error) {
        document.body.append(createPopup([error instanceof Error ? error.message : String(error)]));
        return;
    }

    let leaderId = 1;
    const leadersCorrespondent = Object.entries(datas.leaders ?? {})
        .filter(([, leaderValue]) => leaderValue.toLowerCase().includes(leaderName.toLowerCase()))
        .map(([leaderIdValue]) => Number(leaderIdValue));

    if (leadersCorrespondent.length === 0) {
        document.body.append(createPopup(['Leader non trouvé. Leader réglé par défaut : Directeur Krennic']));
    } else if (leadersCorrespondent.length > 1) {
        document.body.append(createPopup(['Plusieurs leaders trouvés. Leader réglé par défaut : ' + leadersCorrespondent[0]]));
        leaderId = leadersCorrespondent[0];
    } else {
        leaderId = leadersCorrespondent[0];
    }

    let baseColorId = 1;
    const basesCorrespondent = Object.entries(datas.bases ?? {})
        .map(([colorName, officialName], index) => ({ id: index + 1, colorName, officialName }))
        .filter(({ colorName, officialName }) => {
            const normalizedBaseInput = baseInput.toLowerCase();
            return normalizedBaseInput === colorName.toLowerCase() || normalizedBaseInput === officialName.toLowerCase();
        });

    if (baseInput) {
        if (basesCorrespondent.length === 0) {
            document.body.append(createPopup(['Base non trouvée. Base réglée par défaut : Rouge, Agressivité']));
        } else if (basesCorrespondent.length > 1) {
            document.body.append(createPopup(['Plusieurs bases trouvées. Base réglée par défaut : ' + basesCorrespondent[0].colorName]));
            baseColorId = basesCorrespondent[0].id;
        } else {
            baseColorId = basesCorrespondent[0].id;
        }
    }

    const currentDeck = {};
    const entryWrappers = cardArea.querySelectorAll('.entry-wrapper');
    if (entryWrappers.length === 0) {
        document.body.append(createPopup(['Ajoutez au moins une carte avant de créer le deck.']));
        return;
    }

    entryWrappers.forEach((wrapper) => {
        const cardId = wrapper.querySelector('select[name="card[]"]').value;
        const exemplaires = Number(wrapper.querySelector('select[name="quantity[]"]').value);
        currentDeck[cardId] = exemplaires;
    });

    await requestApi(
        'add_deck',
        {
            deck: JSON.stringify(currentDeck),
            name: name,
            leader_id: leaderId,
            base_color_id: baseColorId,
            version: version
        }, 
        (data) => {
            document.body.append(createPopup(['Le deck a été ajouté avec succes !']));
        }
    );
});