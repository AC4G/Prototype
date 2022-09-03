let xhrNickname = new XMLHttpRequest();
let nickchgbox = document.getElementById('nickname-change-box');
let nickchgbutton = document.getElementById('nick-chg-button');
let discardnickchg = document.getElementById('discardnickchg');
let nickname = document.getElementById('nickname-input');
let nicknameSubmit = document.getElementById('nickname-submit');
let nicknameLoading = document.getElementById('nickname-loading');
let nicknameAvailable = document.getElementById('nickname-available');
let nicknameNotAvailable = document.getElementById('nickname-not-available');
let nicknameButton = document.getElementById('nickname-button');
var blur = document.getElementById('blur');
let typingTimer;
let message;

nickchgbutton.addEventListener('click', function (){
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    nickchgbox.style.opacity = '1'
    nickchgbox.style.zIndex = '110'
});

discardnickchg.addEventListener('click', function () {
    setToDefaultNick()
});

blur.addEventListener('click', function () {
    setToDefaultNick()
});

function setToDefaultNick() {
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    nickchgbox.style.zIndex = '-1'
    nickchgbox.style.opacity = '0'
}

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

        nicknameNotAvailable.style.opacity = '1';
        nicknameNotAvailable.style.zIndex = '110';
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
