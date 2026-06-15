/*
/js/searchGame.js
*/

// Références DOM
const backBtn = document.querySelector('#back-btn');
const submitBtn = document.querySelector('#submit-btn');
const select1 = document.querySelector('#select1');
const select2 = document.querySelector('#select2');

// Fonction utilitaire
/**
 * @param {string} action - L'action à requeter auprès de l'api
 * @param {Object} params - Les parametres à passer à l'api
 * @param {function} callback - Un callback à executer avec la réponse api
 * @returns {boolean} false en cas d'erreur, true en cas de réussite
 */
function requestApi (action, params = {}, callback = (data) => { }) {
    if (typeof params === 'function') {
        callback = params;
        params = {};
    }
    let uri = `/api.php?action=${encodeURIComponent(action)}`;
    Object.keys(params).forEach(key => {
        uri += `&${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`;
    });

    return fetch(uri, { method: 'GET' })
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                callback(data);
                return true;
            } else {
                const error = data.error ?? "L'api n'a pas spécifié l'erreur";
                alert('Erreur lors de la requete: ' + error);
                return false;
            }
        })
        .catch(error => {
            alert('Erreur lors de la requete: ' + error.message);
            return false;
        });
}

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});

submitBtn.addEventListener('click', (event) => {
    event.preventDefault();
    const selectedRadio = document.querySelector('input[name="winningLeader"]:checked');
    const winningLeader = selectedRadio ? selectedRadio.value : 'nobodyWon';

    const params = {};
    if (select1.value !== 'all') {
        params.leader1 = select1.value;
    }
    if (select2.value !== 'all') {
        params.leader2 = select2.value;
    }
    if (winningLeader !== 'nobodyWon') {
        params.winningLeader = winningLeader;
    }
    requestApi('get_games', params, (data) => {
        console.log('API get_games réponse:', data);
        alert(JSON.stringify(data));
    });
})