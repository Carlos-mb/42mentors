// Buscador del directorio de mentores: filtra en el navegador por texto, proyecto y «conectados ahora».
(function () {
    var list = document.getElementById('mentor-list');
    if (!list) return;
    var q = document.getElementById('q');
    var project = document.getElementById('project');
    var online = document.getElementById('online');
    var count = document.getElementById('mentor-count');
    var empty = document.getElementById('mentor-empty');
    var items = Array.prototype.slice.call(list.children);

    // Sin mayúsculas ni tildes: «alvaro» encuentra «Álvaro»
    function normalize(text) {
        return text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }
    items.forEach(function (li) {
        li.searchText = normalize(li.dataset.search || '');
    });

    function apply() {
        var text = normalize(q.value.trim());
        var projectId = project.value;
        var shown = 0;
        items.forEach(function (li) {
            var visible = (text === '' || li.searchText.indexOf(text) !== -1)
                && (projectId === '' || li.dataset.projects.indexOf(',' + projectId + ',') !== -1)
                && (!online.checked || li.dataset.online === '1');
            li.hidden = !visible;
            if (visible) shown++;
        });
        count.textContent = shown === 1 ? '1 mentor' : shown + ' mentores';
        empty.hidden = shown !== 0;
    }

    q.addEventListener('input', apply);
    project.addEventListener('change', apply);
    online.addEventListener('change', apply);
    apply(); // por si el navegador recuerda los filtros al volver atrás
})();
