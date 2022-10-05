let nicknamerespbox = document.getElementById('nickname-responsive-box');
let emailrespbox = document.getElementById('email-responsive-box');
let pwdresp = document.getElementById('password-responsive');
profileResize();

window.addEventListener('resize', function () {
    profileResize();

    if (window.innerWidth > 600) {
        nicknamerespbox.style.width = '460px';
        emailrespbox.style.width = '460px';
        pwdresp.style.width = '460px';
    }
});

function profileResize()
{
    if (window.innerWidth < 600) {
        nicknamerespbox.style.width = '400px';
        emailrespbox.style.width = '400px';
        pwdresp.style.width = '400px';
    }

    if (window.innerWidth < 450) {
        nicknamerespbox.style.width = '360px';
        emailrespbox.style.width = '360px';
        pwdresp.style.width = '360px';
    }

    if (window.innerWidth < 400) {
        nicknamerespbox.style.width = '340px';
        emailrespbox.style.width = '340px';
        pwdresp.style.width = '340px';
    }

    if (window.innerWidth < 380) {
        nicknamerespbox.style.width = '300px';
        emailrespbox.style.width = '300px';
        pwdresp.style.width = '300px';
    }

    if (window.innerWidth < 350) {
        nicknamerespbox.style.width = '260px';
        emailrespbox.style.width = '260px';
        pwdresp.style.width = '260px';
    }

}