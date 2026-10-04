<?php
// Política de privacidad y borrado de datos (condiciones de uso de la API de 42, art. 4.2, 4.4 y 4.5).
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    csrf_check();
    // mentor_projects y votes (los que emitió y los que recibió) se borran en cascada
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);
    // La caché de ubicaciones puede contener su puesto: se descarta
    foreach (glob(ROOT_DIR . '/cache/locations_*.json') ?: [] as $file) {
        unlink($file);
    }
    forget_profile($user['id']);
    $_SESSION['user']['consented'] = false;
    flash('Tus datos se han borrado: ya no apareces como mentor y tus valoraciones se han eliminado.');
    redirect('privacy.php');
}

render_header('Privacidad');
?>
<section class="card">
    <h1>Privacidad</h1>

    <h2>Qué datos guardamos</h2>
    <p>Solo de quienes lo aceptan para ser mentores o para valorar a un mentor, y solo después de aceptarlo:</p>
    <ul>
        <li>Identificador y login de 42.</li>
        <li>Campus principal.</li>
        <li>Proyectos que se ofrecen a mentorizar y, si el mentor la escribe, una nota sobre cada uno.</li>
        <li>Lo que el mentor escribe en su perfil, todo opcional: presentación, disponibilidad, forma de contacto
            preferida e idiomas.</li>
        <li>Valoraciones «Me ayudó»: quién valora a qué mentor, por qué proyecto, con cuántos puntos (de 1 a 3)
            y cuándo. Son anónimas: los demás solo ven los puntos totales de cada mentor, nunca quién ha votado
            ni cuándo.</li>
        <li>Fecha del consentimiento («mentor desde») y del último acceso.</li>
    </ul>
    <p>El nombre, la foto, el nivel, la coalición, el puesto en el cluster y las notas de los proyectos de la intra
        no se guardan en la base de datos: se consultan a la API de 42. Para no saturarla, se mantienen en una caché
        temporal del servidor, solo los de los mentores: los puestos, unos minutos; el resto, como mucho una hora.
        Al borrar tus datos se borra también tu caché. El token de acceso de 42 solo vive en tu sesión
        y desaparece al salir.</p>

    <h2>Para qué</h2>
    <p>Únicamente para que estudiantes de 42 encuentren mentores para sus proyectos. Los datos solo son visibles
        para usuarios que han iniciado sesión con 42. No se ceden ni se usan con fines comerciales.</p>

    <h2>Tus derechos</h2>
    <p>Desde <a href="profile.php">Mi perfil</a> puedes cambiar lo que escribiste, ponerte «en pausa» para no
        aparecer en las búsquedas o desmarcar todos los proyectos. En <a href="ratings.php">Mis valoraciones</a>
        puedes ver y cambiar las tuyas. Con el botón de abajo se borran todos tus datos, también las valoraciones
        que hayas hecho y las que hayas recibido. Si cerramos el servicio, borraremos la base de datos.</p>

    <?php if ($user && !empty($user['consented'])): ?>
        <form method="post" onsubmit="return confirm('¿Seguro? Se borrarán tus datos y tus valoraciones, y dejarás de aparecer como mentor.');">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn btn-danger">Borrar mis datos</button>
        </form>
    <?php elseif ($user): ?>
        <p class="muted">No tenemos ningún dato tuyo guardado.</p>
    <?php else: ?>
        <p class="muted">Inicia sesión para gestionar tus datos.</p>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
