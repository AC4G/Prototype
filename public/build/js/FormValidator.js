export default function FormValidator(formName, inputs) {
    const errors = {};

    function isInputValid(event, name) {
        if (!event.target[name]['value']) {
            errors[name] = true;
            event.target[name].style.borderColor = '#ff5447';
        }

        if (event.target[name]['value'].length > 0 && event.target[name].style.borderColor) {
            event.target[name].style.borderColor = 'transparent';
        }
    }

    function handleSubmit(event) {
        event.preventDefault();

        Object.values(inputs).forEach(input => {
            isInputValid(event, input);
        });

        if (Object.keys(errors).length > 0) {
            Object.keys(errors).forEach(key => delete errors[key]);
            return;
        }

        event.target.submit();
    }

    const form = document.getElementById(formName);
    form.addEventListener('submit', handleSubmit);
}
