<?php
// Consentimiento previo para guardar datos (condiciones de uso de la API de 42, art. 3.1 y 4).
// Lo piden tanto quien se ofrece como mentor (profile.php) como quien valora a un mentor (vote.php).
// El texto está en src/views/consent.es.php y consent.en.php: si cambias uno, cambia el otro.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();

// Adónde volver después: solo páginas conocidas de la web
$next = $_POST['next'] ?? $_GET['next'] ?? '';
if (!is_string($next) || !preg_match('/^(index\.php|profile\.php|ratings\.php|available\.php|mentor-manguito\.php|soy-manguito\.php|mentor\.php\?login=[A-Za-z0-9_-]{1,64})$/', $next)) {
    $next = 'profile.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['accept'] ?? '') === '1') {
        // La casilla es obligatoria también en el servidor
        if (($_POST['agree'] ?? '') !== '1') {
            flash(__('Marca la casilla para confirmar que has leído la política de privacidad y que aceptas.'));
            redirect('consent.php?next=' . rawurlencode($next));
        }
        // consented_at («mentor desde») no cambia si ya existía: solo se actualiza la versión aceptada
        $stmt = db()->prepare(
            'INSERT INTO users (id, login, campus_id, consented_at, consent_version, last_login_at)
             VALUES (?, ?, ?, NOW(), ?, NOW())
             ON DUPLICATE KEY UPDATE login = VALUES(login), campus_id = VALUES(campus_id),
                                     consent_version = VALUES(consent_version), last_login_at = NOW()'
        );
        $stmt->execute([$user['id'], $user['login'], $user['campus_id'], PRIVACY_VERSION]);
        $_SESSION['user']['consented'] = true;
        flash(match ($next) {
            'index.php'     => __('Gracias. Has aceptado la política de privacidad actualizada.'),
            'profile.php'   => __('Gracias. Ya puedes completar tu perfil y elegir qué proyectos mentorizas.'),
            'available.php' => __('Gracias. Ya puedes anunciar cuándo estás disponible.'),
            'mentor-manguito.php', 'soy-manguito.php' => __('Gracias. Ya puedes confirmar tu marca.'),
            default         => __('Gracias. Ya puedes valorar a los mentores que te ayuden.'),
        });
        redirect($next);
    }
    redirect('index.php');
}

if (!empty($user['consented'])) {
    redirect($next);
}

render_header(__('Antes de empezar'), $next === 'profile.php' ? 'profile' : (in_array($next, ['available.php', 'mentor-manguito.php', 'soy-manguito.php'], true) ? 'available' : ''));
require ROOT_DIR . '/src/views/consent.' . current_lang() . '.php';
render_footer();
