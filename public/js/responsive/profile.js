let nicknamerespbox = document.getElementById('nickname-responsive-box');
let emailrespbox = document.getElementById('email-responsive-box');
let pwdresp = document.getElementById('password-responsive');
let profilebarresp = document.getElementById('profile-bar-responsive');
let profiletop = document.getElementById('profile-top');

profileResize();

window.addEventListener('resize', function () {
    profileResize();

    if (window.innerWidth > 600) {
        nicknamerespbox.style.width = '460px';
        emailrespbox.style.width = '460px';
        pwdresp.style.width = '460px';
    }

    if (window.innerWidth > 700) {
        profilebarresp.style.width = '80%';
    }

});

function profileResize()
{
    if (window.innerWidth < 1900) {
        profiletop.style.maxWidth = '800px';
    }

    if (window.innerWidth < 1800) {
        profiletop.style.maxWidth = '700px';
    }

    if (window.innerWidth < 1700) {
        profiletop.style.maxWidth = '600px';
    }

    if (window.innerWidth < 1600) {
        profiletop.style.maxWidth = '500px';
    }

    if (window.innerWidth < 1160) {
        profiletop.style.maxWidth = '400px';
    }

    if (window.innerWidth < 700) {
        profilebarresp.style.width = '94%';
    }

    if (window.innerWidth < 600) {
        nicknamerespbox.style.width = '400px';
        emailrespbox.style.width = '400px';
        pwdresp.style.width = '400px';
    }

    if (window.innerWidth < 550) {
        profiletop.style.maxWidth = '300px';
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
        profiletop.style.maxWidth = '200px';
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