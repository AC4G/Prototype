let navbar = document.getElementById('navbar');
let profilemenu = document.getElementById('profile-menu');
var blur = document.getElementById('blur');
let menuclose = document.getElementById('menu-close');
let trigger1 = document.getElementById('trigger1');
let trigger2 = document.getElementById('trigger2');

collisionDetection();

window.addEventListener('resize', function () {
    trigger2.style.position = 'absolute';

    collisionDetection();
});

profilemenu.addEventListener('click', function (){
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    navbar.style.zIndex = '110'
    navbar.style.opacity = '1'
});

blur.addEventListener('click', function () {
    navbarOnClose();
});

menuclose.addEventListener('click', function () {
    navbarOnClose();
});

function navbarOnClose()
{
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    navbar.style.opacity = '0'
    navbar.style.zIndex = '-1'
}

function collisionDetection()
{
    if (trigger1.getBoundingClientRect().y + trigger1.offsetHeight >= trigger2.getBoundingClientRect().y) {
        trigger2.style.position = 'relative';
    }
}