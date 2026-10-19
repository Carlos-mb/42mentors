<?php
// Consentimiento previo para guardar datos (condiciones de uso de la API de 42, art. 3.1 y 4).
// Lo piden tanto quien se ofrece como mentor (profile.php) como quien valora a un mentor (vote.php).
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
            flash('Marca la casilla para confirmar que has leído la política de privacidad y que aceptas.');
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
            'index.php'     => 'Gracias. Has aceptado la política de privacidad actualizada.',
            'profile.php'   => 'Gracias. Ya puedes completar tu perfil y elegir qué proyectos mentorizas.',
            'available.php' => 'Gracias. Ya puedes anunciar cuándo estás disponible.',
            'mentor-manguito.php', 'soy-manguito.php' => 'Gracias. Ya puedes confirmar tu marca.',
            default         => 'Gracias. Ya puedes valorar a los mentores que te ayuden.',
        });
        redirect($next);
    }
    redirect('index.php');
}

if (!empty($user['consented'])) {
    redirect($next);
}

render_header('Antes de empezar', $next === 'profile.php' ? 'profile' : (in_array($next, ['available.php', 'mentor-manguito.php', 'soy-manguito.php'], true) ? 'available' : ''));
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
        <?php if (votes_enabled()): ?>
            <li>Si valoras a un mentor con «Me ayudó»: a quién, por qué proyecto, los puntos (de 1 a 3) y la fecha.</li>
        <?php endif; ?>
    </ul>
    <?php if ($next === 'mentor-manguito.php' || ($user['role'] ?? null) === 'mentor'): ?>
        <p>Como mentor oficial de la Operación Manguito, guardamos esa marca y hasta qué hora has dicho que estás
            disponible. La ven los estudiantes de tu campus que hayan iniciado sesión, en «Disponibles ahora», junto
            con tu nombre, tu foto y tu puesto en el cluster.</p>
    <?php elseif ($next === 'soy-manguito.php'): ?>
        <p>Si te marcas como manguito, guardamos esa marca. Solo la ven tú y el responsable de la web.</p>
    <?php endif; ?>
    <?php if (votes_enabled()): ?>
        <p>Las valoraciones son anónimas: los demás solo ven los puntos totales de cada mentor, nunca quién ha votado.
            Tú puedes ver y cambiar las tuyas en «Mis valoraciones».</p>
    <?php endif; ?>
    <p>Si te ofreces como mentor, los estudiantes de tu campus que hayan iniciado sesión con 42 verán en tu ficha:</p>
    <ul>
        <li>Lo que escribas en tu perfil y los proyectos que mentorizas.</li>
        <li>Tu nombre, tu foto, tu nivel, tu coalición, tu puesto en el cluster si estás conectado y la nota
            y la fecha de validación de los proyectos que mentorizas.</li>
        <?php if (votes_enabled()): ?>
            <li>Los puntos «Me ayudó» que hayas recibido.</li>
        <?php endif; ?>
    </ul>
    <p>Esos datos de la intra se consultan a la API de 42 y no se guardan en la base de datos. Para no saturar
        la API, se guarda una copia en una caché temporal del servidor, que se actualiza con la intra como mucho
        una vez por hora cuando alguien los consulta, y que se borra si borras tus datos.</p>
    <p>Puedes ponerte «en pausa», o retirar tu consentimiento y borrar tus datos cuando quieras, desde la
        <a href="privacy.php" target="_blank" rel="noopener">página de privacidad</a>. Se borran también las
        valoraciones que hayas hecho y las que hayas recibido. Si pasas <?= USER_RETENTION_MONTHS ?> meses sin
        entrar, se borran solos.</p>
    <form method="post" class="actions">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <label class="check">
            <input type="checkbox" name="agree" value="1" required>
            He leído la <a href="privacy.php" target="_blank" rel="noopener">política de privacidad y las condiciones
            de uso</a> y acepto que se guarden estos datos.
        </label>
        <button type="submit" name="accept" value="1" class="btn">Acepto</button>
        <button type="submit" name="accept" value="0" formnovalidate class="link">No, gracias</button>
    </form>
</section>
<?php render_footer(); ?>
