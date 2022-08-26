let xhrNickname = new XMLHttpRequest();
let nickname = document.getElementById('nickname-input');
let nicknameSubmit = document.getElementById('nickname-submit');
let nicknameLoading = document.getElementById('nickname-loading');
let nicknameAvailable = document.getElementById('nickname-available');
let nicknameNotAvailable = document.getElementById('nickname-not-available');
let nicknameButton = document.getElementById('nickname-button');
let typingTimer;
let message;

nickname.addEventListener('keyup', function () {
    if (nickname.value.length < 1) {
        setNicknameDefault();
        return false;
    }

    nicknameLoading.style.zIndex = '110';
    nicknameLoading.style.opacity = '1';

    clearTimeout(2000);
    typingTimer = setTimeout(doneNicknameTyping, 2000);
});

nickname.addEventListener('keydown', function () {
    if (nickname.value.length > 1) {
        setNicknameDefault();
    }

    clearTimeout(typingTimer);
});

function doneNicknameTyping() {
    if (nickname.value.length < 1) {
        return false;
    }

    xhrNickname.open('GET', '/api/nickname/' + nickname.value);
    xhrNickname.send();
}

xhrNickname.onreadystatechange = function () {
    if (xhrNickname.readyState === 4) {
        message = Object.values(JSON.parse(xhrNickname.responseText))[2];
        onNicknameResponse(message);
    }
};

function onNicknameResponse(message) {
    if (message === 0) {
        setNicknameDefault();

        nicknameAvailable.style.zIndex = '110';
        nicknameAvailable.style.opacity = '1';
        nicknameSubmit.classList.remove('cursor-not-allowed');
        nicknameButton.style.pointerEvents = 'auto';
        nicknameButton.style.cursor = 'pointer';
    }

    if (message === 1) {
        setNicknameDefault();

        nicknameNotAvailable.style.zIndex = '110';
        nicknameNotAvailable.style.opacity = '1';
    }
}

function setNicknameDefault() {
    nicknameLoading.style.opacity = '0';
    nicknameLoading.style.zIndex = '-1';
    nicknameAvailable.style.zIndex = '-1';
    nicknameAvailable.style.opacity = '0';
    nicknameNotAvailable.style.opacity = '0';
    nicknameNotAvailable.style.zIndex = '-1';
    nicknameButton.style.pointerEvents = 'none';
    nicknameButton.style.removeProperty('cursor');
    nicknameSubmit.classList.add('cursor-not-allowed');
}