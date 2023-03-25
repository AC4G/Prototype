function redirectToItem (
    buttonId
)
{
    let itemId = buttonId.split('_')[0];

    let form = document.createElement('form');
    form.name = 'itemSettingsForm';
    form.method = 'POST';

    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'itemIdInput';

    form.appendChild(input);
    document.body.appendChild(form);

    input.value = itemId;

    form.action = '/dashboard/creator/item';
    form.submit();
}