let privacyButton = document.getElementById('privacy-button');
let privacy = document.getElementById('privacy');
let form = document.getElementById('form-privacy');

privacyButton.addEventListener('click', function () {
    form.submit();
});