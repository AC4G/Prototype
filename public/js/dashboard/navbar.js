let navbar = document.getElementById('navbar');

if (window.innerWidth < 1000) {
    navbar.classList.add('hidden');
}

window.addEventListener('resize', function () {
    if (window.innerWidth < 1000) {
        navbar.classList.add('hidden');
    }

    if (window.innerWidth > 1000 && navbar.classList.contains('hidden')) {
        navbar.classList.remove('hidden');
    }
});