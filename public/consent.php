<?php
// Consentimiento previo para guardar datos (condiciones de uso de la API de 42, art. 3.1 y 4).
// Lo piden tanto quien se ofrece como mentor (profile.php) como quien valora a un mentor (vote.php).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();

// Adónde volver después: solo páginas conocidas de la web
$next = $_POST['next'] ?? $_GET['next'] ?? '';
if (!is_string($next) || !preg_match('/^(profile\.php|ratings\.php|mentor\.php\?login=[A-Za-z0-9_-]{1,64})$/', $next)) {
    $next = 'profile.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['accept'] ?? '') === '1') {
        $stmt = db()->prepare(
            'INSERT INTO users (id, login, campus_id, consented_at, last_login_at)
             VALUES (?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE login = VALUES(login), campus_id = VALUES(campus_id), last_login_at = NOW()'
        );
        $stmt->execute([$user['id'], $user['login'], $user['campus_id']]);
        $_SESSION['user']['consented'] = true;
        flash($next === 'profile.php'
            ? 'Gracias. Ya puedes completar tu perfil y elegir qué proyectos mentorizas.'
            : 'Gracias. Ya puedes valorar a los mentores que te ayuden.');
        redirect($next);
    }
    redirect('index.php');
}

if (!empty($user['consented'])) {
    redirect($next);
}

render_header('Antes de empezar', $next === 'profile.php' ? 'profile' : '');
?>
<section class="card">
    <h1>Antes de guardar tus datos</h1>
    <p>Para ofrecerte como mentor o para valorar a un mentor necesitamos guardar algunos datos tuyos.
        Guardamos <strong>solo esto</strong>:</p>
    <ul>
        <li>Tu identificador y tu login de 42.</li>
        <li>Tu campus principal, para mostrarte solo mentores y estudiantes de tu campus.</li>
        <li>Si te ofreces como mentor: los proyectos que marques como «puedo mentorizar» y lo que tú escribas en tu
            perfil, todo opcional (presentación, disponibilidad, cómo prefieres que te contacten, idiomas y notas
            sobre los proyectos).</li>
        <li>Si valoras a un mentor con «Me ayudó»: a quién, por qué proyecto, los puntos (de 1 a 3) y la fecha.</li>
    </ul>
    <p>Las valoraciones son anónimas: los demás solo ven los puntos totales de cada mentor, nunca quién ha votado.
        Tú puedes ver y cambiar las tuyas en «Mis valoraciones».</p>
    <p>Si te ofreces como mentor, los estudiantes de tu campus que hayan iniciado sesión con 42 verán en tu ficha:</p>
    <ul>
        <li>Lo que escribas en tu perfil y los proyectos que mentorizas.</li>
        <li>Tu nombre, tu foto, tu nivel, tu coalición, tu puesto en el cluster si estás conectado y la nota
            y la fecha de validación de los proyectos que mentorizas.</li>
        <li>Los puntos «Me ayudó» que hayas recibido.</li>
    </ul>
    <p>Esos datos de la intra se consultan a la API de 42 y no se guardan en la base de datos: solo se mantienen
        como mucho una hora en una caché temporal del servidor, que se borra si borras tus datos.</p>
    <p>Puedes ponerte «en pausa», o retirar tu consentimiento y borrar tus datos cuando quieras, desde la
        <a href="privacy.php">página de privacidad</a>. Se borran también las valoraciones que hayas hecho y las
        que hayas recibido.</p>
    <form method="post" class="actions">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <button type="submit" name="accept" value="1" class="btn">Acepto</button>
        <button type="submit" name="accept" value="0" class="link">No, gracias</button>
    </form>
</section>
<?php render_footer(); ?>
