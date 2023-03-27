let cannotScanQRCodeButton = document.getElementById('cannot-scan-qr-code');
let codeTwoFa = document.getElementById('code-2fa');

let twoFaRecoveryTokens = document.querySelectorAll('.token');
let copiedNotification = document.getElementById('copied');

cannotScanQRCodeButton.addEventListener('click', function () {
    codeTwoFa.classList.remove('hidden')
});

async function copyRecoveryTokensToClipBoard() {
    if (window.isSecureContext && navigator.clipboard) {
        let tokens = '';

        twoFaRecoveryTokens.forEach((token, index) => {
            tokens += token.textContent;

            if ((index + 1) % 2 === 0) {
                tokens += '\n';
            }

            tokens += ' ';
        });

        await navigator.clipboard.writeText(tokens);
        await showCopiedNotification();
    }
}

async function showCopiedNotification() {
    copiedNotification.style.opacity = '1';
    await delay(1000);
    copiedNotification.style.opacity = '0';
}

function delay(time) {
    return new Promise(resolve => setTimeout(resolve, time));
}
