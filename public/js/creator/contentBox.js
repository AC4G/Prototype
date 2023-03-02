let contentBox = document.getElementById('content-box');

resizeContentBox();

window.addEventListener('resize', function () {
    resizeContentBox();
});

function resizeContentBox()
{
    contentBox.style.maxWidth = (window.innerWidth * 0.6).toString() + 'px';
}