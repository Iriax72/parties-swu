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
            let errorMessage = `Erreur HTTP: ${response.status}`;
            if (!response.ok) {
                throw new Error(response.statusText || `HTTP ${response.status}`);
                try {
                    const errorBody = await response.json();
                    if (errorBody?.error) {
                        errorMessage = errorBody.error;
                    }
                } catch (e) {
                    // Ignorer
                }
                throw new Error(errorMessage)
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                callback(data);
                return true;
            }

            const error = data?.error ?? "L'api n'a pas spécifié l'erreur";
            throw new Error(error);
        })
        .catch((error) => {
            const message = error instanceof Error ? error.message : String(error);
            console.error('Erreur lors de la requete: ', error);

            alert(message || 'Erreur inconnue');
            return false;
        });
}

/**
 * @returns {JSON} Le fichier json /datas.json
 */
export async function getDatas() {
    const dataPromise = (async () => {
        const response = await fetch('/datas.json');
        if (!response.ok) {
            throw new Error(`Impossible de lire /datas.json: ${response.status}`);
        }
        return response.json();
    })().then((loadedDatas) => {
        return loadedDatas;
    }).catch((error) => {
        throw new Error('erreur lors du chargement des données' + error.message);
    });
}

/**
 * 
 * @param {Array} content - Un array de string ou d'HTMLNodes enfants de la popup
 * @returns {HTMLElement} La popup créée
 */
export function createPopup(content) {
    const popup = document.createElement('div');
    popup.classList.add('popup');

    const crossBtn = document.createElement('button');
    crossBtn.textContent = 'X';
    crossBtn.classList.add('btn', 'back-a');
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