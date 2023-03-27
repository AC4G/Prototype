let twofaButton = document.getElementById('2fa-button');
let twofaBox = document.getElementById('2fa-box');
let closeTwofaBox = document.getElementById('close-2fa-box');
let cancelTwofaBox = document.getElementById('cancel-2fa-box');
var blur = document.getElementById('blur');

twofaButton.addEventListener('click', function () {
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    twofaBox.style.opacity = '1'
    twofaBox.style.zIndex = '110'
});

blur.addEventListener('click', function () {
    closeBox()
});

closeTwofaBox.addEventListener('click', function () {
    closeBox()
});

cancelTwofaBox.addEventListener('click', function () {
   closeBox()
});

function closeBox() {
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    twofaBox.style.zIndex = '-1'
    twofaBox.style.opacity = '0'
}
