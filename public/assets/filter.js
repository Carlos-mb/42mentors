// Filtra al escribir: <input data-filter="#lista"> oculta los <li data-name> que no coinciden.
document.querySelectorAll('input[data-filter]').forEach(function (input) {
    var list = document.querySelector(input.dataset.filter);
    if (!list) return;
    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        list.querySelectorAll('li[data-name]').forEach(function (li) {
            li.hidden = q !== '' && li.dataset.name.indexOf(q) === -1;
        });
    });
});
