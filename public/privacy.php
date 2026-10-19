<?php
// Política de privacidad, condiciones de uso y borrado de datos (condiciones de uso de la API de 42, art. 4.2, 4.4 y 4.5).
// El texto está en src/views/privacy.es.php y privacy.en.php: si cambias uno, cambia el otro y sube
// PRIVACY_VERSION y PRIVACY_UPDATED en src/auth.php.
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    csrf_check();
    // mentor_projects y votes (los que emitió y los que recibió) se borran en cascada
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);
    $_SESSION['user']['role'] = null;
    // La caché de ubicaciones puede contener su puesto: se descarta
    foreach (glob(ROOT_DIR . '/cache/locations_*.json') ?: [] as $file) {
        unlink($file);
    }
    forget_profile($user['id']);
    $_SESSION['user']['consented'] = false;
    flash(__('Tus datos se han borrado: ya no apareces como mentor y tus valoraciones se han eliminado.'));
    redirect('privacy.php');
}

render_header(__('Privacidad y condiciones de uso'));
$retention = USER_RETENTION_MONTHS;
require ROOT_DIR . '/src/views/privacy.' . current_lang() . '.php';
render_footer();
