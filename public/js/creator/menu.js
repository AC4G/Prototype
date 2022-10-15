let createMenu = document.getElementById('create-menu');
let createButton = document.getElementById('create-button');

createMenu.addEventListener('click', function(event) {
    event.stopPropagation();
});

createButton.addEventListener('click', function(event) {
    event.stopPropagation();
    createMenu.style.visibility = 'visible';
    createMenu.style.opacity = '1';
});

window.addEventListener('click', function() {
    if (createMenu.style.visibility === 'visible') {
        createMenu.style.opacity = '0';
        createMenu.style.visibility = 'hidden';
    }
});



