/*
/js/functions.js

Contient toutes les fonctions utilitaires
*/

/**
 * @param {string} action - L'action à requêter auprès de l'api
 * @param {object} params - Des paramètres à fournir à l'api
 * @param {function} callback - Un callback à executer avec le réultat
 * @returns {boolean} false en cas d'erreur, true en cas de réussite
*/
export function requestApi(action, params = {}, callback = (data)=>{ }) {
    // permettre d'appeler requestApi(uri, callback)
    if (typeof params === 'function') {
        callback = params;
        params = {};
    }

    // définir l'uri à partir de l'action et des params
    let uri = `/api.php?action=${encodeURIComponent(action)}`;
    Object.keys(params).forEach((key) => {
        uri += `&${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`;
    });

    // retourner le résultat de la requete
    return fetch(uri, { method: 'GET' })
        .then(async (response) => {
            let data;
            try {
                data = await response.json();
            } catch (error) {
                data = null;
            }

            if (!response.ok) {
                const serverError = data?.error ?? data?.message ?? response.statusText ?? `HTTP ${response.status}`;
                throw new Error(`Échec de la requête "${action}" (${response.status}) : ${serverError}`);
            }

            if (!data?.success) {
                const serverError = data?.error ?? data?.message ?? "L'api n'a pas spécifié l'erreur";
                throw new Error(`La requête "${action}" a échoué : ${serverError}`);
            }

            return data;
        })
        .then((data) => {
            callback(data);
            return true;
        })
        .catch((error) => {
            const message = error instanceof Error ? error.message : String(error);
            console.error(`Erreur lors de la requete "${action}":`, error);

            alert(`Erreur API (${action}) : ${message || 'Erreur inconnue'}`);
            return false;
        });
}

/**
 * @returns {JSON} Le fichier json /datas.json
 */
export async function getDatas() {
    try {
        const response = await fetch('/datas.json');
        if (!response.ok) {
            throw new Error(`Impossible de lire /datas.json: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        throw new Error(`erreur lors du chargement des données: ${error instanceof Error ? error.message : String(error)}`);
    }
}

/**
 * @param {Array} content - Un array de string ou d'HTMLNodes enfants de la popup
 * @returns {HTMLElement} La popup créée
 */
export function createPopup(content) {
    const popup = document.createElement('div');
    popup.classList.add('popup');

    const crossBtn = document.createElement('button');
    crossBtn.textContent = 'X';
    crossBtn.classList.add('btn', 'back-anchor');
    crossBtn.addEventListener('click', () => {
        popup.remove();
    });

    popup.append(crossBtn);

    content.forEach((element) => {
        popup.append(element); // TODO sécuriser ça
        popup.append(document.createElement('br'));
    });

    return popup;
}

/**
 * @param {Array} elements - Un array d'éléments à mettre dans la box
 * @param {String | null} href - Un lien vers lequel rediriger quand la box est cliquée, facultatif
 * @returns {HTMLElement} La box créée
 */
export function createBox(elements, href = null) {
    const box = document.createElement(href ? 'a' : 'div');
    box.classList.add('box');
    if (href) {
        box.href = href;
    }
    elements.forEach(element => {
        const span = document.createElement('span');
        span.textContent = String(element);
        box.append(span);
    });
    return box;
}