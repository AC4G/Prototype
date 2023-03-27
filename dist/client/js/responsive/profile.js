let nicknamerespbox = document.getElementById('nickname-responsive-box');
let emailrespbox = document.getElementById('email-responsive-box');
let securityresp = document.getElementById('security-responsive');
profileResize();

window.addEventListener('resize', function () {
    profileResize();

    if (window.innerWidth > 600) {
        nicknamerespbox.style.width = '460px';
        emailrespbox.style.width = '460px';
        securityresp.style.width = '460px';
    }
});

function profileResize()
{
    if (window.innerWidth < 600) {
        nicknamerespbox.style.width = '400px';
        emailrespbox.style.width = '400px';
        securityresp.style.width = '400px';
    }

    if (window.innerWidth < 450) {
        nicknamerespbox.style.width = '360px';
        emailrespbox.style.width = '360px';
        securityresp.style.width = '360px';
    }

    if (window.innerWidth < 400) {
        nicknamerespbox.style.width = '340px';
        emailrespbox.style.width = '340px';
        securityresp.style.width = '340px';
    }

    if (window.innerWidth < 380) {
        nicknamerespbox.style.width = '300px';
        emailrespbox.style.width = '300px';
        securityresp.style.width = '300px';
    }

    if (window.innerWidth < 350) {
        nicknamerespbox.style.width = '260px';
        emailrespbox.style.width = '260px';
        securityresp.style.width = '260px';
    }

}