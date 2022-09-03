let nicknamerespbox = document.getElementById('nickname-responsive-box');
let emailrespbox = document.getElementById('email-responsive-box');
let pwdresp = document.getElementById('password-responsive');
let profilebarresp = document.getElementById('profile-bar-responsive');

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

function profileResize() {
    if (window.innerWidth < 600) {
        nicknamerespbox.style.width = '400px';
        emailrespbox.style.width = '400px';
        pwdresp.style.width = '400px';
    }

    if (window.innerWidth < 700) {
        profilebarresp.style.width = '94%';
    }
}