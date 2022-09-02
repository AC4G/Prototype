let discardPasswordChg = document.getElementById('discardPasswordchg');
let passwordchgbutton = document.getElementById('change-password-button');
let passwordchgbox = document.getElementById('password-chg-box');
let passwordinput1 = document.getElementById('password-input-1');
let passwordinput2 = document.getElementById('password-input-2');
let passwordnotallowed = document.getElementById('password-not-allowed');
let passwordallowed = document.getElementById('password-allowed');
let passwordloading = document.getElementById('password-loading');
let passwordsubmit = document.getElementById('password-submit');
let passwordbutton = document.getElementById('password-button');
var blur = document.getElementById('blur');

passwordchgbutton.addEventListener('click', function () {
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    passwordchgbox.style.opacity = '1'
    passwordchgbox.style.zIndex = '110'
});

blur.addEventListener('click', function () {
    setToDefaultPassword()
    setPasswordDefault()
    passwordinput1.value = ''
    passwordinput2.value = ''
});

discardPasswordChg.addEventListener('click', function () {
    setToDefaultPassword()
});

function setToDefaultPassword() {
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    passwordchgbox.style.zIndex = '-1'
    passwordchgbox.style.opacity = '0'
}

passwordinput1.addEventListener('keyup', function () {
    if (passwordinput1.value.length < 1) {
        setPasswordDefault()
        return false;
    }

    if (passwordinput2.value.length > 0) {
        passwordloading.style.zIndex = '110';
        passwordloading.style.opacity = '1';
    }

    clearTimeout(1000);
    typingTimer = setTimeout(donePasswordTyping, 1000);
});

passwordinput1.addEventListener('keydown', function () {
    if (passwordinput1.value.length > 1) {
        setPasswordDefault()
    }

    clearTimeout(typingTimer);
});

passwordinput2.addEventListener('keyup', function () {
    if (passwordinput2.value.length < 1) {
        setPasswordDefault()
        return false;
    }

    if (passwordinput1.value.length > 0) {
        passwordloading.style.zIndex = '110';
        passwordloading.style.opacity = '1';
    }

    clearTimeout(1000);
    typingTimer = setTimeout(donePasswordTyping, 1000);
});

passwordinput2.addEventListener('keydown', function () {
    if (passwordinput2.value.length > 1) {
        setPasswordDefault()
    }

    clearTimeout(typingTimer);
});

function donePasswordTyping() {
    passwordloading.style.zIndex = '-1';
    passwordloading.style.opacity = '0';

    if (passwordinput1.value.length < 1 && passwordinput2.value.length < 1 || passwordinput1.value.length > 0 && passwordinput2.value.length === 0 || passwordinput1.value.length === 0 && passwordinput2.value.length > 0) {
        return;
    }

    if (passwordinput1.value !== passwordinput2.value) {
        passwordnotallowed.style.opacity = '1';
        passwordnotallowed.style.zIndex = '110';
    }

    if (passwordinput1.value === passwordinput2.value && passwordinput1.value.length < 10) {
        passwordnotallowed.style.opacity = '1';
        passwordnotallowed.style.zIndex = '110';
    }

    if (passwordinput1.value === passwordinput2.value && passwordinput1.value.length > 10) {
        passwordallowed.style.opacity = '1';
        passwordallowed.style.zIndex = '110';
        passwordsubmit.classList.remove('cursor-not-allowed');
        passwordbutton.style.pointerEvents = 'auto';
        passwordbutton.style.cursor = 'pointer';
    }
}

function setPasswordDefault() {
    passwordloading.style.zIndex = '-1';
    passwordloading.style.opacity = '0';
    passwordnotallowed.style.opacity = '0';
    passwordnotallowed.style.zIndex = '-1';
    passwordallowed.style.opacity = '0';
    passwordallowed.style.zIndex = '-1';
    passwordsubmit.classList.add('cursor-not-allowed');
    passwordbutton.style.pointerEvents = 'none';
    passwordbutton.style.cursor = 'none';
}

