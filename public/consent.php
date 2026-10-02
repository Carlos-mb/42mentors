<?php
// Consentimiento previo para guardar datos (condiciones de uso de la API de 42, art. 3.1 y 4).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();

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
        flash('Gracias. Ya puedes completar tu perfil y elegir qué proyectos mentorizas.');
        redirect('profile.php');
    }
    redirect('index.php');
}

if (!empty($user['consented'])) {
    redirect('profile.php');
}

render_header('Antes de empezar', 'profile');
?>
<section class="card">
    <h1>Antes de ofrecerte como mentor</h1>
    <p>Para que otros estudiantes puedan encontrarte guardamos <strong>solo esto</strong>:</p>
    <ul>
        <li>Tu identificador y tu login de 42.</li>
        <li>Tu campus principal, para mostrarte solo a estudiantes de tu campus.</li>
        <li>Los proyectos que marques como «puedo mentorizar».</li>
        <li>Lo que tú escribas en tu perfil, todo opcional: presentación, disponibilidad, cómo prefieres que
            te contacten, idiomas y notas sobre los proyectos.</li>
    </ul>
    <p>Mientras seas mentor, los estudiantes de tu campus que hayan iniciado sesión con 42 verán en tu ficha:</p>
    <ul>
        <li>Lo anterior.</li>
        <li>Tu nombre, tu foto, tu nivel, tu coalición, tu puesto en el cluster si estás conectado y la nota
            y la fecha de validación de los proyectos que mentorizas.</li>
    </ul>
    <p>Esos datos se consultan a la intra y no se guardan en la base de datos: solo se mantienen como mucho
        una hora en una caché temporal del servidor, que se borra si borras tus datos.</p>
    <p>Puedes ponerte «en pausa», o retirar tu consentimiento y borrar tus datos cuando quieras, desde la
        <a href="privacy.php">página de privacidad</a>.</p>
    <form method="post" class="actions">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button type="submit" name="accept" value="1" class="btn">Acepto</button>
        <button type="submit" name="accept" value="0" class="link">No, gracias</button>
    </form>
</section>
<?php render_footer(); ?>
