let cannotScanQRCodeButton = document.getElementById('cannot-scan-qr-code');
let codeTwoFa = document.getElementById('code-2fa');

cannotScanQRCodeButton.addEventListener('click', function () {
    codeTwoFa.classList.remove('hidden')
});