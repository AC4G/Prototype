function closeFlashMessage(element) {
    $(element).fadeOut('slow', function() {
        $(this).remove();
    });
}

function autoCloseFlashMessage(element) {
    setTimeout(function() {
        $(element).fadeOut('slow', function() {
            $(this).remove();
        });
    }, 10000);
}

$(document).ready(function() {
    $('.flash').each(function() {
        autoCloseFlashMessage(this);
    });
});