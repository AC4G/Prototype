let contentBox = document.getElementById('content-box');

resizeContentBox();

window.addEventListener('resize', function () {
    resizeContentBox();
});

function resizeContentBox()
{
    let maxWidth = (window.innerWidth * 0.6).toString() + 'px';
    contentBox.style.maxWidth = maxWidth;
}