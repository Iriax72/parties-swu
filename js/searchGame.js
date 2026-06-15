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
    const uri = `/api.php?action=${action}`;
    for (let i = 0 ; i < params.length ; i++) {
        uri = uri.concat(`&${params.keys()[i]}=${params[i]}`);
    }
    fetch(`/api.php?action=${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(data);
            return true;
        } else {
            const error = data.error ?? "L'api n'a pas spécifié l'erreur";
            alert('Erreur lors de la requete' + error);
            return false;
        }
    })
    .catch(error => {
        alert('Erreur lors de la requete' + error);
        return false;
    });
}

// EventListeners
backBtn.addEventListener('click', () => {
    window.location.assign('/menu.php');
});

submitBtn.addEventListener('click', (event) => {
    event.preventDefault();
    
    const selectedRadio = document.querySelector['input[name="winningLeader"]:checked'];
    const winningLeader = selectedRadio.value;

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
        alert(data);
    });
})