let authorize = document.getElementById('o_auth_form_authorize');
let oauthForm = document.getElementById('o_auth_form');
let errorBox = document.getElementById('error-box');
window.addEventListener("load", (event) => {
    if (errorBox) {
        authorize.style.pointerEvents = 'none';
        oauthForm.classList.add('cursor-not-allowed');
    }
});