let navbar = document.getElementById('navbar');
let profilemenu = document.getElementById('profile-menu');
var blur = document.getElementById('blur');
let navbarlinkhome = document.getElementById('navbar-link-home');
let menuclose = document.getElementById('menu-close');

if (window.innerWidth < 1000) {
    navbarDefault();
}

profilemenu.addEventListener('click', function (){
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    navbar.classList.add('absolute');
    navbar.style.zIndex = '110'
    navbar.style.opacity = '1';
});

blur.addEventListener('click', function () {
    navbarOnClose();
});

menuclose.addEventListener('click', function () {
    navbarOnClose();
});

window.addEventListener('resize', function () {
    if (blur.style.opacity === '0.3') {
        return;
    }

    if (!navbar.classList.contains('absolute')) {
        profilemenu.classList.add('hidden');
        navbarlinkhome.style.width = '94%';
        menuclose.classList.add('hidden');
    }

    if (window.innerWidth < 1000) {
        navbarDefault();
    }

    if (window.innerWidth > 1000 && navbar.classList.contains('absolute')) {
        navbarOnChange();
    }
});

function navbarDefault()
{
    profilemenu.classList.remove('hidden');
    navbar.classList.add('absolute');
    navbar.style.zIndex = '-1';
    navbar.style.opacity = '0';
    navbarlinkhome.style.width = '78%';
    menuclose.classList.remove('hidden');
}

function navbarOnChange()
{
    profilemenu.classList.add('hidden');
    navbar.style.zIndex = '0';
    navbar.style.opacity = '1';
    navbar.classList.remove('absolute');
    navbarlinkhome.style.width = '94%';
    menuclose.classList.add('hidden');
}

function navbarOnClose()
{
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    navbar.classList.remove('absolute');
    if (window.innerWidth < 1000) {
        navbar.classList.add('absolute');
        navbar.style.zIndex = '-1';
        navbar.style.opacity = '0';
    }

    if (window.innerWidth > 1000) {
        profilemenu.classList.add('hidden');
        navbarlinkhome.style.width = '94%';
        menuclose.classList.add('hidden');
    }
}