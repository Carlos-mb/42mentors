<?php
/**
 * Texto de la política de privacidad en español (lo incluye public/privacy.php). La versión en inglés es privacy.en.php:
 * si cambias una, cambia la otra y sube PRIVACY_VERSION y PRIVACY_UPDATED en src/auth.php.
 * @var ?array $user  @var int $retention
 */
?>
<section class="card">
    <h1>Privacidad y condiciones de uso</h1>
    <p class="muted">Versión <?= PRIVACY_VERSION ?> · Última actualización: <?= e(long_date(PRIVACY_UPDATED)) ?></p>

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
    </ul>
    <p>Si te declaras mentor oficial o manguito de la Operación Manguito (con un enlace privado que da el responsable
        de la web), guardamos esa marca, y la hora hasta la que dices que estás disponible si eres mentor. Un mentor
        oficial lo ven los estudiantes de su campus que han iniciado sesión, en «Disponibles ahora», con su nombre,
        su foto y su puesto en el cluster. Quien es manguito solo lo ven esa persona y el responsable. Esta marca no
        la ha pedido ni la gestiona 42 Madrid. Puedes quitártela cuando quieras desde esa misma página, y el
        responsable puede quitársela a quien haga un uso indebido.</p>
    <?php if (!votes_enabled()): ?>
        <p>Las valoraciones «Me ayudó» están desactivadas ahora. Las que ya estaban guardadas se conservan sin mostrarse
            y se borran con «Borrar mis datos».</p>
    <?php endif; ?>

    <h2>Qué datos leemos de la API de 42</h2>
    <p>Al iniciar sesión, la intra nos da, de ti, tu login, tu nombre, tu foto, tu campus y su zona horaria, y tus
        proyectos con su estado y si están validados. Es lo que usamos para saber quién eres, qué proyectos has
        terminado y a qué campus perteneces. Todo eso vive solo en tu sesión y no se guarda en la base de datos.</p>
    <p>De los mentores que han aceptado consultamos, cuando alguien abre su ficha o una lista, su nombre, su foto, su
        nivel, su coalición, su puesto en el cluster y la nota y la fecha de validación de los proyectos que mentorizan.
        No se guardan en la base de datos.</p>
    <p>Para no saturar la API, esos datos de los mentores se mantienen en una caché temporal del servidor: los
        puestos se renuevan cada pocos minutos, y el resto, como mucho una vez por hora cuando alguien los consulta.
        El token de acceso de 42 solo vive en tu sesión y desaparece al salir.</p>

    <h2>Base jurídica</h2>
    <p>Tu consentimiento (art. 6.1.a del RGPD). Lo das en la pantalla «Antes de guardar tus datos», marcando la
        casilla y pulsando «Acepto»; queda registrado con su fecha y la versión de esta política. Lo mismo vale para
        marcarte como mentor oficial o manguito: solo puedes hacerlo después de aceptar. Los datos de la
        intra que se muestran de un mentor (nombre, foto, nivel…) solo se muestran si ese mentor ha dado su
        consentimiento. Puedes retirarlo en cualquier momento, sin que afecte a lo anterior.</p>
    <p>Si cambia de forma relevante lo que guardamos o cómo lo usamos, se te pedirá aceptar de nuevo la política.</p>

    <h2>Quién puede usar la web</h2>
    <p>Cualquier persona con una cuenta de la red de 42 puede iniciar sesión con ella. Cada persona solo ve mentores
        y estudiantes de su campus principal. Quien no tiene cuenta de 42 no puede entrar.</p>

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
        <li>Si cerramos el servicio, borraremos la base de datos.</li>
    </ul>

    <h2>Seguridad</h2>
    <ul>
        <li>La conexión va cifrada (HTTPS, con HSTS).</li>
        <li>El token de acceso de 42 solo vive en tu sesión en el servidor: no se guarda en la base de datos y
            desaparece al salir o al caducar.</li>
        <li>Las cookies son <code>Secure</code>, <code>HttpOnly</code> y <code>SameSite</code>.</li>
        <li>Todas las páginas con datos exigen haber iniciado sesión con 42, y los formularios llevan protección
            contra peticiones falsas (CSRF).</li>
        <li>Las claves de la aplicación y de la base de datos están fuera de la parte pública del servidor y no se
            publican en el repositorio del código.</li>
        <li>Guardamos lo mínimo: lo que ves en «Qué datos guardamos».</li>
        <li>El proveedor de alojamiento hace copias de seguridad automáticas del servidor. Si borras tus datos,
            pueden seguir en esas copias hasta que el proveedor las sustituya por otras más recientes.</li>
    </ul>
    <p>Si hubiera una brecha de seguridad que afectara a datos personales, se avisaría a 42 y, cuando proceda, a las
        personas afectadas, y se revocarían las credenciales comprometidas.</p>

    <h2>Cookies y almacenamiento local</h2>
    <p>Usamos dos cookies y ninguna guarda datos personales:</p>
    <ul>
        <li>La de sesión (<code>ft_mentors</code>), necesaria para mantener tu sesión iniciada: se borra al cerrar el
            navegador o al salir.</li>
        <li>La de idioma (<code>lang</code>): solo se crea si eliges un idioma en el selector de arriba, y guarda esa
            elección («es» o «en») durante un año. Sin ella, la web usa el idioma de tu navegador.</li>
    </ul>
    <p>No usamos cookies de analítica ni de publicidad, ni rastreadores de terceros, ni guardamos nada más en tu
        navegador (<code>localStorage</code>).</p>

    <h2>Terceros</h2>
    <p>No se ceden datos a terceros. Solo intervienen estos:</p>
    <ul>
        <li>La API de 42 (la intra), con la que inicias sesión y de la que consultamos los datos que se explican
            arriba, según las condiciones de 42.</li>
        <li><code>cdn.intra.42.fr</code>, la CDN de 42: las fotos de perfil se cargan desde ahí, así que tu navegador
            le envía datos técnicos de conexión (como tu IP) al pedirlas.</li>
        <li>El proveedor de alojamiento del servidor, que trata datos técnicos de conexión (IP y cabeceras de las
            peticiones) en sus registros.</li>
    </ul>

    <h2>Tus derechos</h2>
    <p>Puedes ejercer los derechos de los arts. 15 a 22 del RGPD. Los de acceso, portabilidad, rectificación y
        supresión los tienes a mano en la web; para cualquier otro, escribe al contacto de arriba.</p>
    <ul>
        <li><strong>Acceso:</strong> con el botón «Descargar mis datos» de abajo obtienes una copia de todo lo que
            guardamos sobre ti.</li>
        <li><strong>Rectificación:</strong> lo que escribiste lo cambias en <a href="profile.php">Mi perfil</a>; los
            datos que vienen de la intra se corrigen allí. Tus valoraciones las ves y cambias en
            <a href="ratings.php">Mis valoraciones</a>.</li>
        <li><strong>Supresión:</strong> con el botón de abajo se borran todos tus datos, también las valoraciones que
            hayas hecho y las que hayas recibido.</li>
        <li><strong>Limitación y oposición:</strong> puedes ponerte «en pausa» para no aparecer en las búsquedas, o
            desmarcar todos los proyectos. Si quieres algo más, escríbenos.</li>
        <li><strong>Portabilidad:</strong> esa misma descarga es un fichero JSON, un formato estructurado y de uso
            común. Incluye lo que guardamos de ti; no incluye los datos de la intra, que no se guardan, ni quién te ha
            valorado, porque esas valoraciones son anónimas (solo los puntos totales).</li>
        <li><strong>Retirar tu consentimiento:</strong> en cualquier momento, borrando tus datos; no afecta a lo
            anterior.</li>
    </ul>
    <p>Si crees que no tratamos bien tus datos, puedes reclamar ante la Agencia Española de Protección de Datos
        (AEPD, <a href="https://www.aepd.es" target="_blank" rel="noopener">www.aepd.es</a>).</p>

    <?php if ($user && user_has_data($user['id'])): ?>
        <div class="actions">
            <form method="post" action="download.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn">Descargar mis datos</button>
            </form>
            <form method="post" onsubmit="return confirm('¿Seguro? Se borrarán tus datos y tus valoraciones, y dejarás de aparecer como mentor.');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-danger">Borrar mis datos</button>
            </form>
        </div>
    <?php elseif ($user): ?>
        <p class="muted">No tenemos ningún dato tuyo guardado.</p>
    <?php else: ?>
        <p class="muted">Inicia sesión para gestionar tus datos.</p>
    <?php endif; ?>
</section>
