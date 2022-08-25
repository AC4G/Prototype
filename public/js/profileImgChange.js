let prfpicinput = document.getElementById('prf-pic-input');
let imgprwbox = document.getElementById('imgprwbox');
let imgprw = document.getElementById('imgprw');
let discardprfimg = document.getElementById('discardprfimg');
let saveprfimg = document.getElementById('saveprfimg');
let blur = document.getElementById('blur');
const imgTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/jpg'];
let error = document.getElementById('picture-error');
let imgSubmit = document.getElementById('img-submit');
let imgSubmitBox = document.getElementById('img-submit-box');
let nickchgbox = document.getElementById('nickname-change-box');
let nickchgbutton = document.getElementById('nick-chg-button');
let discardnickchg = document.getElementById('discardnickchg');

nickchgbutton.addEventListener('click', function (){
    blur.style.opacity = '0.3'
    blur.style.zIndex = '100'
    nickchgbox.style.opacity = '1'
    nickchgbox.style.zIndex = '110'
});

prfpicinput.onchange = evt => {
    const [file] = prfpicinput.files
    if (file) {
        imgprw.src = URL.createObjectURL(file)
        blur.style.opacity = '0.3'
        blur.style.zIndex = '100'
        imgprwbox.style.opacity = '1'
        imgprwbox.style.zIndex = '110'

        if (!imgTypes.includes(file['type'])) {
            error.style.display = 'block'
            imgSubmit.style.pointerEvents = 'none'
            imgSubmitBox.style.bottom = '22px'
            imgSubmitBox.classList.add('cursor-not-allowed')
        }
    }
}

function setToDefault() {
    nickchgbox.style.zIndex = '-1'
    nickchgbox.style.opacity = '0'
    imgprwbox.style.zIndex = '-1'
    imgprwbox.style.opacity = '0'
    blur.style.opacity = '0'
    blur.style.zIndex = '-1'
    error.style.display = 'none'
    nicknameLoading.style.display = 'none';
    imgSubmitBox.style.removeProperty('bottom')
    imgSubmit.style.removeProperty('pointer-events')
    imgSubmitBox.classList.remove('cursor-not-allowed')
}

blur.addEventListener('click', function handleClick() {
    setToDefault();
});

discardprfimg.addEventListener('click', function handleClick() {
    setToDefault();
});

discardnickchg.addEventListener('click', function () {
    setToDefault()
});
