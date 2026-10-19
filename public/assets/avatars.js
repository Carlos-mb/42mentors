// Si la foto de la intra no carga (su CDN a veces la rechaza), se muestra un círculo con la inicial del login.
(function () {
    function fallback(img) {
        if (img.dataset.initial === undefined) return;
        const span = document.createElement('span');
        span.className = img.className + ' placeholder';
        span.textContent = img.dataset.initial;
        img.replaceWith(span);
    }
    // «error» no sube por el árbol: se escucha en la fase de captura
    document.addEventListener('error', function (e) {
        if (e.target.tagName === 'IMG') fallback(e.target);
    }, true);
    // Las que ya fallaron antes de cargar este script
    document.querySelectorAll('img[data-initial]').forEach(function (img) {
        if (img.complete && img.naturalWidth === 0) fallback(img);
    });
})();
