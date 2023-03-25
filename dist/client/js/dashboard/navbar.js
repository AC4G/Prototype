let navbar = document.getElementById('navbar');
let menu = document.getElementById('menu');
var blur = document.getElementById('blur');
let menuclose = document.getElementById('menu-close');
let trigger1 = document.getElementById('trigger1');
let trigger2 = document.getElementById('trigger2');

collisionDetection();

window.addEventListener('resize', function () {
    trigger2.style.position = 'absolute';

    collisionDetection();
});

menu.addEventListener('click', function (){
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    navbar.style.left = '0px'
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
    navbar.style.left = '-320px'
}

function collisionDetection()
{
    if (trigger1.getBoundingClientRect().y + trigger1.offsetHeight >= trigger2.getBoundingClientRect().y) {
        trigger2.style.position = 'relative';
    }
}