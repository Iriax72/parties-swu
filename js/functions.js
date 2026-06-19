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
        params = {}
    }
    // définir l'uri à partir de l'action et des params
    let uri = `/api.php?action=${encodeURIComponent(action)}`;
    Object.keys(params).forEach(key => {
        uri += `&${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`;
    });
    // retourner le résultat de la requete
    return fetch(uri, {method: 'GET'})
    .then(response => {
        if (!response.ok) throw new Error(response.statusText);
        return response.json();
    })
    .then(data => {
        if (data.succes) {
            callback(data);
            return true;
        } else {
            const error = data?.error ?? "L'api n'a pas spécifié l'erreur";
            alert('Erreur lors de la requete: ' + error);
            return false;
        }
    })
    .catch(error => {
        alert('Erreur lors de la requete: ' + error.getMessage());
        return false;
    });
}

function createPopup(content) {
    // content est un array de string / HTMLNodes à mettre dans la popup
    const popup = document.createElement('div');
    popup.classList.add('popup');
    const crossBtn = document.createElement('btn');
    crossBtn.textContent = 'X';
    crossBtn.classList.add('btn', 'back-btn');
    crossBtn.addEventListener('click', () => {
        popup.remove();
    })
    popup.append(crossBtn);

    content.forEach(element => {
        popup.append(element); // TODO sécuriser ça
        popup.append(document.createElement('br'));
    });

    return popup
}