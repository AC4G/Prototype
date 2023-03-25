let createMenu = document.getElementById('create-menu');
let createButton = document.getElementById('create-button');

createMenu.addEventListener('click', function(event) {
    event.stopPropagation();
});

createButton.addEventListener('click', function(event) {
    event.stopPropagation();

    if (createMenu.style.visibility === 'visible') {
        setToDefault();
        return;
    }

    createMenu.style.visibility = 'visible';
    createMenu.style.opacity = '1';
});

window.addEventListener('click', function() {
    if (createMenu.style.visibility === 'visible') {
        setToDefault();
    }
});

function setToDefault()
{
    createMenu.style.opacity = '0';
    createMenu.style.visibility = 'hidden';
}

function openItemMenu(
    buttonId
)
{
    let itemMenu = getItemId(buttonId);

    itemMenu.style.visibility = 'visible';
    itemMenu.style.opacity = '1';
}

function closeItemMenu(
    buttonId
)
{
    let itemMenu = getItemId(buttonId);

    itemMenu.style.opacity = '0';
    itemMenu.style.visibility = 'hidden';
}

function getItemId (
    buttonId
)
{
    const itemName = buttonId.split('_')[0];
    return document.getElementById(itemName + '_menu');
}


