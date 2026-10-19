<?php
// Política de privacidad, condiciones de uso y borrado de datos (condiciones de uso de la API de 42, art. 4.2, 4.4 y 4.5).
// Si cambias el texto, sube PRIVACY_VERSION y PRIVACY_UPDATED en src/auth.php.
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    csrf_check();
    // mentor_projects y votes (los que emitió y los que recibió) se borran en cascada
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);
    // También sale de la lista de mentores oficiales y manguitos de la administración
    db()->prepare('DELETE FROM roster WHERE login = ?')->execute([$user['login']]);
    $_SESSION['user']['role'] = null;
    // La caché de ubicaciones puede contener su puesto: se descarta
    foreach (glob(ROOT_DIR . '/cache/locations_*.json') ?: [] as $file) {
        unlink($file);
    }
    forget_profile($user['id']);
    $_SESSION['user']['consented'] = false;
    flash('Tus datos se han borrado: ya no apareces como mentor y tus valoraciones se han eliminado.');
    redirect('privacy.php');
}

render_header('Privacidad y condiciones de uso');
$retention = USER_RETENTION_MONTHS;
?>
<section class="card">
    <h1>Privacidad y condiciones de uso</h1>
    <p class="muted">Versión <?= PRIVACY_VERSION ?> · Última actualización: <?= e(PRIVACY_UPDATED) ?></p>

    <h2>Responsable y contacto</h2>
    <p>Carlos Melero (login <code>cmelero-</code>) es el responsable de esta web, desarrollada con la API de 42 conforme a
        sus Condiciones Generales de Uso. Para ejercer tus derechos, retirar tu consentimiento o cualquier consulta
        sobre tus datos, escribe a <a href="mailto:42mentors@carlosmelero.com">42mentors@carlosmelero.com</a>.
        Se responde en un plazo máximo de un mes.</p>

    <h2>Para qué</h2>
    <p>Únicamente para que estudiantes de 42 encuentren mentores para sus proyectos. Los datos solo son visibles
        para usuarios que han iniciado sesión con 42. No se ceden ni se usan con fines comerciales, ni para
        publicidad, ni para decisiones automatizadas que te afecten.</p>

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
        <li>Registro de tu aceptación: la fecha («mentor desde») y la versión de esta política que aceptaste.</li>
        <li>Fecha del último acceso.</li>
        <li>Si eres mentor oficial: hasta qué hora has dicho que estás disponible. La ven los manguitos de tu campus.</li>
    </ul>
    <p>Además, la administración de 42 Madrid mantiene una lista de mentores oficiales y manguitos con el login y
        el rol de cada persona. Esa lista se guarda aunque no hayas aceptado nada y no se muestra a nadie hasta que
        aceptas. Quien es manguito solo lo ven esa persona y la administración. Si borras tus datos, te quitamos
        también de esa lista: la administración tendría que volver a añadirte.</p>
    <p>El nombre, la foto, el nivel, la coalición, el puesto en el cluster y las notas de los proyectos de la intra
        no se guardan en la base de datos: se consultan a la API de 42. Para no saturarla, se mantienen en una caché
        temporal del servidor, solo los de los mentores: los puestos se renuevan cada pocos minutos, y el resto,
        como mucho una vez por hora cuando alguien los consulta. El token de acceso de 42 solo vive en tu sesión
        y desaparece al salir.</p>

    <?php if (!votes_enabled()): ?>
        <p>Las valoraciones «Me ayudó» están desactivadas ahora. Las que ya estaban guardadas se conservan sin mostrarse
            y se borran con «Borrar mis datos».</p>
    <?php endif; ?>

    <h2>Base jurídica</h2>
    <p>Tu consentimiento (art. 6.1.a del RGPD). Lo das en la pantalla «Antes de guardar tus datos», marcando la
        casilla y pulsando «Acepto»; queda registrado con su fecha y la versión de esta política. Los datos de la
        intra que se muestran de un mentor (nombre, foto, nivel…) solo se muestran si ese mentor ha dado su
        consentimiento. Puedes retirarlo en cualquier momento, sin que afecte a lo anterior.</p>
    <p>Si cambia de forma relevante lo que guardamos o cómo lo usamos, se te pedirá aceptar de nuevo la política.</p>

    <h2>Condiciones de uso</h2>
    <ul>
        <li>Es para quienes forman parte de la red de 42 y han iniciado sesión con su cuenta de 42.</li>
        <li>Los datos de esta web vienen de la API de 42 y de la comunidad de mentores, y deben quedarse dentro de la
            red de 42: no los compartas, publiques ni redistribuyas fuera.</li>
        <li>Está prohibido extraer datos de forma masiva (scraping), automatizar el acceso o intentar saltarse los
            controles de acceso o los límites de la API.</li>
        <li>Tu sesión es personal: no la compartas ni des acceso a terceros.</li>
        <li>Lo que escribas en tu perfil (presentación, notas) debe ser respetuoso y no contener datos de otras personas.</li>
        <li>No hay garantía sobre la ayuda de los mentores: la mentoría es voluntaria. Los datos pueden tener minutos
            u horas de retraso, y la fuente oficial es siempre la intra.</li>
        <li>El uso indebido puede suponer la retirada del acceso y su comunicación a 42.</li>
    </ul>

    <h2>Cuánto tiempo los guardamos</h2>
    <ul>
        <li>Tu perfil, los proyectos que mentorizas, tus valoraciones (emitidas y recibidas) y el registro de tu
            aceptación: mientras sigas usando la web. Si pasas <strong><?= $retention ?> meses sin iniciar
            sesión</strong>, se borran automáticamente. Antes, cuando tú quieras, con «Borrar mis datos».</li>
        <li>Caché de datos de la intra (nombre, foto, nivel…): se renueva con la intra como se explica arriba y se
            borra cuando se borran tus datos.</li>
        <li>Sesión: hasta que salgas o caduque tu token de acceso de 42 (normalmente a las 2 horas).</li>
        <li>Lista de la administración: hasta que la administración te quite o borres tus datos.</li>
        <li>Si cerramos el servicio, borraremos la base de datos.</li>
    </ul>

    <h2>Tus derechos</h2>
    <p>Desde <a href="profile.php">Mi perfil</a> puedes cambiar lo que escribiste, ponerte «en pausa» para no
        aparecer en las búsquedas o desmarcar todos los proyectos. En <a href="ratings.php">Mis valoraciones</a>
        puedes ver y cambiar las tuyas. Con el botón de abajo se borran todos tus datos, también las valoraciones
        que hayas hecho y las que hayas recibido. Para saber qué datos tuyos tenemos, pedir una copia, oponerte a
        un tratamiento o cualquier otra cuestión del RGPD, escribe al contacto de arriba.</p>

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
