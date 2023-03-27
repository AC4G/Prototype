const xhrNickname = new XMLHttpRequest();
const nickchgform = document.getElementById('nick-chg-form');
const nickchgbox = document.getElementById('nickname-change-box');
const nickchgbutton = document.getElementById('nick-chg-button');
const discardnickchg = document.getElementById('discardnickchg');
const nickname = document.getElementById('nickname-input');
const nicknameSubmit = document.getElementById('nickname-submit');
const nicknameLoading = document.getElementById('nickname-loading');
const nicknameAvailable = document.getElementById('nickname-available');
const nicknameNotAvailable = document.getElementById('nickname-not-available');
const nicknameButton = document.getElementById('nickname-button');
var blur = document.getElementById('blur');
let typingTimer;
let message;

nickchgbutton.addEventListener('click', function () {
    show(blur, 0.3, 100);
    show(nickchgbox);
});

nickchgform.addEventListener('keypress', function (event) {
   if (event.key === 'Enter' && nicknameSubmit.classList.contains('cursor-not-allowed')) {
       event.preventDefault();
   }
});

discardnickchg.addEventListener('click', setToDefaultNick);

blur.addEventListener('click', setToDefaultNick);

nickname.addEventListener('keyup', function () {
    if (nickname.value.length < 1) {
        setNicknameDefault();
        return false;
    }

    show(nicknameLoading);

    clearTimeout(2000);
    typingTimer = setTimeout(doneNicknameTyping, 2000);
});

nickname.addEventListener('keydown', function () {
    if (nickname.value.length > 1) {
        setNicknameDefault();
    }

    clearTimeout(typingTimer);
});

xhrNickname.onreadystatechange = function () {
    if (xhrNickname.readyState === 4) {
        message = Object.values(JSON.parse(xhrNickname.responseText))[2];
        onNicknameResponse(message);
    }
};

function doneNicknameTyping() {
    if (nickname.value.length < 1) {
        return false;
    }

    xhrNickname.open('GET', '/api/nickname/' + nickname.value);
    xhrNickname.send();
}

function setToDefaultNick() {
    hide(blur);
    hide(nickchgbox);
}

function onNicknameResponse(message) {
    setNicknameDefault();

    if (message === 0) {
        show(nicknameAvailable);
    }

    if (message === 1) {
        show(nicknameNotAvailable);
    }
}

function setNicknameDefault() {
    hide(nicknameLoading);
    hide(nicknameAvailable)
    hide(nicknameNotAvailable);
    hide(nicknameButton);
}

function show(element, opacity = 1, zIndex = 110) {
    element.style.opacity =  opacity.toString();
    element.style.zIndex = zIndex.toString();

    if (element === nicknameAvailable) {
        nicknameSubmit.classList.remove('cursor-not-allowed');
        nicknameButton.style.pointerEvents = 'auto';
        nicknameButton.style.cursor = 'pointer';
    }
}

function hide(element) {
    if (element === nicknameButton) {
        element.style.pointerEvents = 'none';
        element.style.removeProperty('cursor');
        nicknameSubmit.classList.add('cursor-not-allowed');

        return;
    }

    element.style.opacity = '0';
    element.style.zIndex = '-1';
}
