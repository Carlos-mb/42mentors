<?php
// «Disponibles ahora»: mentores oficiales de mi campus que han avisado de que están disponibles.
// La ven los manguitos, los mentores oficiales (que desde aquí anuncian su disponibilidad) y los administradores.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
if (!can_see_available($user)) {
    render_error(__('Esta página es para manguitos y mentores oficiales.'), 403);
}
$isOfficial = ($user['role'] ?? null) === 'mentor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!$isOfficial) {
        render_error(__('Solo los mentores oficiales pueden anunciar su disponibilidad.'), 403);
    }
    if (empty($user['consented'])) {
        redirect('consent.php?next=available.php');
    }
    // Solo cuenta si en la BD sigue figurando como mentor oficial (el administrador puede habérsela quitado)
    if (($_POST['action'] ?? '') === 'stop') {
        db()->prepare('UPDATE users SET available_until = NULL WHERE id = ?')->execute([$user['id']]);
        flash(__('Ya no apareces como disponible.'));
        redirect('available.php');
    }
    // La hora es la próxima vez que el reloj marque esa hora (si ya pasó hoy, mañana), con un máximo
    $time = is_string($_POST['until'] ?? null) ? $_POST['until'] : '';
    if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $m)) {
        $now = new DateTimeImmutable('now', user_timezone());
        $until = $now->setTime((int) $m[1], (int) $m[2]);
        if ($until <= $now) {
            $until = $until->modify('+1 day');
        }
        if ($until > $now->modify('+' . AVAILABLE_MAX_HOURS . ' hours')) {
            flash(__('Puedes anunciarte como mucho durante {hours} horas seguidas. Elige una hora más cercana.', ['hours' => AVAILABLE_MAX_HOURS]));
        } else {
            db()->prepare("UPDATE users SET available_until = ? WHERE id = ? AND manguito_role = 'mentor'")
                ->execute([$until->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'), $user['id']]);
            flash(__('Apareces como disponible hasta las {time}.', ['time' => $until->format('H:i')]));
        }
    } else {
        flash(__('Escribe una hora válida, por ejemplo 18:30.'));
    }
    redirect('available.php');
}

// Mentores oficiales de mi campus, con consentimiento, que están disponibles ahora
$stmt = db()->prepare(
    "SELECT u.id, u.login, u.bio, u.available_until
       FROM users u
      WHERE u.campus_id <=> ? AND u.available_until > NOW() AND " . sql_is_official()
);
$stmt->execute([$user['campus_id']]);
$mentors = $stmt->fetchAll();

$myUntil = null;
if ($isOfficial && !empty($user['consented'])) {
    $stmt = db()->prepare('SELECT available_until FROM users WHERE id = ? AND available_until > NOW()');
    $stmt->execute([$user['id']]);
    $myUntil = $stmt->fetchColumn() ?: null;
}

$apiOk = enrich_mentors($mentors, $user['campus_id']);
foreach ($mentors as &$mentor) {
    $mentor['availability'] = 'available'; // ha dicho que está disponible: sin etiqueta de «ocupado» o «en pausa»
    $mentor['until_label'] = __('hasta las {time}', ['time' => local_time($mentor['available_until'])]);
    $mentor['is_me'] = (int) $mentor['id'] === $user['id'];
}
unset($mentor);
sort_mentors($mentors);

render_header(__('Disponibles ahora'), 'available');
?>
<?php if ($isOfficial): ?>
    <section class="card">
        <h2><?= e(__('Mi disponibilidad')) ?></h2>
        <?php if (empty($user['consented'])): ?>
            <p><?= e(__('Para anunciar que estás disponible necesitamos que aceptes antes cómo tratamos tus datos.')) ?></p>
            <p class="actions"><a class="btn" href="consent.php?next=available.php"><?= e(__('Continuar')) ?></a></p>
        <?php else: ?>
            <?php if ($myUntil): ?>
                <p><?= _h('Apareces como disponible {until}.', [], ['until' => '<strong>' . e(__('hasta las {time}', ['time' => local_time((string) $myUntil)])) . '</strong>']) ?></p>
            <?php else: ?>
                <p class="muted"><?= e(__('Ahora mismo no apareces como disponible. Avisa a los manguitos de que puedes ayudarles desde ya.')) ?></p>
            <?php endif; ?>
            <form method="post" class="actions">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <label><?= e(__('Disponible hasta las')) ?>
                    <input type="time" name="until" required value="<?= $myUntil ? e(local_time((string) $myUntil)) : '' ?>">
                </label>
                <button type="submit" class="btn"><?= e(__('Guardar')) ?></button>
                <?php if ($myUntil): ?>
                    <button type="submit" name="action" value="stop" formnovalidate class="link"><?= e(__('Ya no estoy disponible')) ?></button>
                <?php endif; ?>
            </form>
            <p class="muted small"><?= e(__('Hora de tu campus ({zone}). Máximo {hours} horas desde ahora; si la hora ya ha pasado hoy, cuenta como mañana.', ['zone' => user_timezone()->getName(), 'hours' => AVAILABLE_MAX_HOURS])) ?></p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h1><?= e(__('Disponibles ahora')) ?></h1>
    <p class="muted"><?= e(__('Mentores oficiales de tu campus que han avisado de que están disponibles. Pulsa uno para ver cómo contactarle.')) ?></p>
    <?php if (!$apiOk): ?>
        <p class="warn"><?= e(__('No se ha podido consultar la intra ahora mismo: faltan nombres, fotos o ubicaciones.')) ?></p>
    <?php endif; ?>

    <?php if (!$mentors): ?>
        <p><?= e(__('Ahora mismo no hay ningún mentor oficial disponible. Vuelve a mirar más tarde.')) ?></p>
    <?php else: ?>
        <ul class="mentors">
            <?php foreach ($mentors as $mentor): ?>
                <li><?php require ROOT_DIR . '/src/views/mentor_card.php'; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p class="actions"><a href="available.php"><?= e(__('Actualizar')) ?></a></p>
</section>
<?php render_footer(); ?>
