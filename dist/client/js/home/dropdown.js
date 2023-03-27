let account = document.getElementById('account');
let dropdown = document.getElementById('account-dropdown');

dropdown.addEventListener('click', function (event) {
    event.stopPropagation();
});

account.addEventListener('click', function (event) {
    event.stopPropagation();

    if (dropdown.style.visibility === 'visible') {
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        return;
    }

    dropdown.style.visibility = 'visible';
    dropdown.style.opacity = '1';
});

window.addEventListener('click', function () {
    if (dropdown.style.visibility === 'visible') {
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
    }
});

