const limit = 20;
const xhrItems = new XMLHttpRequest();
let loadItems = document.getElementById('load-items');
let totalItems = document.getElementById('item-amount');
let totalItemsText = document.getElementById('item-amount-text');
let itemContentBox = document.getElementById('item-content-box');

let currentPage = document.getElementById('currentPage').value;

window.onload = function() {
    getItemsInfo();
};

loadItems.addEventListener('click', function () {
    currentPage.value += 1;
    getItemsInfo();
});

function getItemsInfo()
{
    xhrItems.open('GET', '/api/website/items/' + currentPage + '/' + limit);
    xhrItems.send();
}

xhrItems.onreadystatechange = function () {
    if (xhrItems.readyState === 4) {
        let data = Object.values(JSON.parse(xhrItems.responseText));

        totalItems.innerHTML = data.length.toString();

        if (data.length === 1) {
            totalItemsText.innerHTML = 'Item';
        }


    }
}