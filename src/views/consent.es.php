<?php
/**
 * Texto del consentimiento en español (lo incluye public/consent.php). La versión en inglés es consent.en.php:
 * si cambias una, cambia la otra.
 * @var array $user  @var string $next
 */
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
