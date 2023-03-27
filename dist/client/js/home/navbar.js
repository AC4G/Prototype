let explanationbox = document.getElementById('explanation-box');
let documentationbox = document.getElementById('documentation-box');
let communitybox = document.getElementById('community-box');
let navbar = document.getElementById('navbar');
let name = document.getElementById('name');
let menu = document.getElementById('menu');
var blur = document.getElementById('blur');

resizeComponents();

window.addEventListener('resize', function () {
    resizeComponents();
    resizeToDefault();
});

menu.addEventListener('click', function () {
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    navbar.style.opacity = '1'
    navbar.style.zIndex = '110'
});

blur.addEventListener('click', function () {
    navbarOnClose();
});

function navbarOnClose()
{
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    navbar.style.opacity = '0'
    navbar.style.zIndex = '-1'
}

function resizeComponents()
{
    if (window.innerWidth < 1200) {
        documentationbox.style.marginLeft = '60px';
        documentationbox.style.marginRight = '60px';
    }

    if (window.innerWidth < 900) {
        explanationbox.classList.add('hidden');
        documentationbox.classList.add('hidden');
        communitybox.classList.add('hidden');
        menu.classList.remove('hidden');
        name.style.width = '100%';
        name.style.justifyContent = 'center';
        name.style.left = '0px';
        name.classList.add('flex');
    }
}

function resizeToDefault()
{
    if (window.innerWidth > 1200) {
        documentationbox.style.marginLeft = '127px';
        documentationbox.style.marginRight = '127px';
    }

    if (window.innerWidth > 900) {
        explanationbox.classList.remove('hidden');
        documentationbox.classList.remove('hidden');
        communitybox.classList.remove('hidden');
        menu.classList.add('hidden');
        name.classList.remove('flex');
        name.style.width = null;
        name.style.justifyContent = null;
        name.style.left = null;
    }
}