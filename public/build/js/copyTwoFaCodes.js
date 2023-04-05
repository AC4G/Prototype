const cannotScanQRCodeButton = $('#cannot-scan-qr-code');
const codeTwoFa = $('#code-2fa');
const twoFaRecoveryTokens = $('.token');
const copiedNotification = $('#copied');

cannotScanQRCodeButton.on('click', () => codeTwoFa.removeClass('hidden'));

const copyRecoveryTokensToClipBoard = async () => {
    if (window.isSecureContext && navigator.clipboard) {
        const tokens = Array.from(twoFaRecoveryTokens).map(token => token.textContent).join('\n');

        await navigator.clipboard.writeText(tokens);
        await copiedNotification.fadeIn(200).delay(1000).fadeOut(200);
    }
};