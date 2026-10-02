// Mi perfil.
(function () {
    // El campo de nota solo se muestra en los proyectos marcados
    document.querySelectorAll('.checklist li').forEach(function (li) {
        var box = li.querySelector('input[type=checkbox]');
        var note = li.querySelector('input.note');
        if (!box || !note) return;
        box.addEventListener('change', function () {
            note.hidden = !box.checked;
        });
    });

    // Confirmación tras guardar
    var dialog = document.getElementById('saved-dialog');
    if (dialog) {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            alert('Cambios guardados.');
        }
    }

    // Aviso si se intenta salir con cambios sin guardar
    var form = document.getElementById('profile-form');
    if (!form) return;
    var dirty = false;
    function markDirty(event) {
        if (!event.target.classList.contains('filter')) dirty = true; // filtrar la lista no es un cambio
    }
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (event) {
        if (dirty) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
