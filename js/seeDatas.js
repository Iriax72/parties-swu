/*
/js/seeDatas
*/

// Références DOM
const backBtn = document.querySelector("#back-btn");

//EventListener
backBtn.addEventListener('click', () => {
    window.location.assign("/menu.php")
});