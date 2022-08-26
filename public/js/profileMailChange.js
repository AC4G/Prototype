let xhrEmail = new XMLHttpRequest();
let email = document.getElementById('email-input');
let emailSubmit = document.getElementById('email-submit');
let emailButton = document.getElementById('email-button');
let emailLoading = document.getElementById('email-loading');
let emailAvailable = document.getElementById('email-available');
let emailNotAvailable = document.getElementById('email-not-available');

email.addEventListener('keyup', function () {
    if (email.value.length < 1) {
        setEmailDefault();
        return false;
    }

    emailLoading.style.zIndex = '110';
    emailLoading.style.opacity = '1';

    clearTimeout(2000);
    typingTimer = setTimeout(doneEmailTyping, 2000);
});

email.addEventListener('keydown', function () {
    if (email.value.length > 1) {
        setEmailDefault();
    }

    clearTimeout(typingTimer);
});

function doneEmailTyping() {
    if (email.value.length < 1) {
        return false;
    }

    if (!email.value.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)) {
        emailLoading.style.zIndex = '-1';
        emailLoading.style.opacity = '0';
        emailNotAvailable.style.zIndex = '110';
        emailNotAvailable.style.opacity = '1';
        return false;
    }

    xhrEmail.open('GET', '/api/email/' + email.value);
    xhrEmail.send();
}

xhrEmail.onreadystatechange = function () {
    if (xhrEmail.readyState === 4) {
        message = Object.values(JSON.parse(xhrEmail.responseText))[2];
        onEmailResponse(message);
    }
};

function onEmailResponse() {
    if (message === 0) {
        setEmailDefault();

        emailAvailable.style.zIndex = '110';
        emailAvailable.style.opacity = '1';
        emailSubmit.classList.remove('cursor-not-allowed');
        emailButton.style.pointerEvents = 'auto';
        emailButton.style.cursor = 'pointer';
    }

    if (message === 1) {
        setEmailDefault();

        emailNotAvailable.style.zIndex = '110';
        emailNotAvailable.style.opacity = '1';
    }
}

function setEmailDefault() {
    emailLoading.style.opacity = '0';
    emailLoading.style.zIndex = '-1';
    emailAvailable.style.zIndex = '-1';
    emailAvailable.style.opacity = '0';
    emailNotAvailable.style.opacity = '0';
    emailNotAvailable.style.zIndex = '-1';
    emailButton.style.pointerEvents = 'none';
    emailButton.style.removeProperty('cursor');
    emailSubmit.classList.add('cursor-not-allowed');
}