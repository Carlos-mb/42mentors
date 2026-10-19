<?php
// «Disponibles ahora»: mentores oficiales de mi campus que han avisado de que están disponibles.
// La ven los manguitos, los mentores oficiales (que desde aquí anuncian su disponibilidad) y los administradores.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
if (!can_see_available($user)) {
    render_error('Esta página es para manguitos y mentores oficiales.', 403);
}
$isOfficial = ($user['role'] ?? null) === 'mentor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!$isOfficial) {
        render_error('Solo los mentores oficiales pueden anunciar su disponibilidad.', 403);
    }
    if (empty($user['consented'])) {
        redirect('consent.php?next=available.php');
    }
    $hours = (int) ($_POST['hours'] ?? 0);
    if (in_array($hours, AVAILABLE_HOURS, true)) {
        db()->prepare('UPDATE users SET available_until = NOW() + INTERVAL ? HOUR WHERE id = ?')
            ->execute([$hours, $user['id']]);
        flash('Apareces como disponible durante ' . $hours . ($hours === 1 ? ' hora.' : ' horas.'));
    } else {
        db()->prepare('UPDATE users SET available_until = NULL WHERE id = ?')->execute([$user['id']]);
        flash('Ya no apareces como disponible.');
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
    $mentor['until_label'] = 'hasta las ' . substr((string) $mentor['available_until'], 11, 5);
    $mentor['is_me'] = (int) $mentor['id'] === $user['id'];
}
unset($mentor);
sort_mentors($mentors);

render_header('Disponibles ahora', 'available');
?>
<?php if ($isOfficial): ?>
    <section class="card">
        <h2>Mi disponibilidad</h2>
        <?php if (empty($user['consented'])): ?>
            <p>Para anunciar que estás disponible necesitamos que aceptes antes cómo tratamos tus datos.</p>
            <p class="actions"><a class="btn" href="consent.php?next=available.php">Continuar</a></p>
        <?php else: ?>
            <?php if ($myUntil): ?>
                <p>Apareces como disponible <strong>hasta las <?= e(substr((string) $myUntil, 11, 5)) ?></strong>.</p>
            <?php else: ?>
                <p class="muted">Ahora mismo no apareces como disponible. Avisa a los manguitos de que puedes ayudarles desde ya.</p>
            <?php endif; ?>
            <form method="post" class="actions">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <?php foreach (AVAILABLE_HOURS as $hours): ?>
                    <button type="submit" name="hours" value="<?= $hours ?>" class="btn btn-secondary">
                        Disponible <?= $hours ?> h
                    </button>
                <?php endforeach; ?>
                <?php if ($myUntil): ?>
                    <button type="submit" name="hours" value="0" class="link">Ya no estoy disponible</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h1>Disponibles ahora</h1>
    <p class="muted">Mentores oficiales de tu campus que han avisado de que están disponibles. Pulsa uno para ver
        cómo contactarle.</p>
    <?php if (!$apiOk): ?>
        <p class="warn">No se ha podido consultar la intra ahora mismo: faltan nombres, fotos o ubicaciones.</p>
    <?php endif; ?>

    <?php if (!$mentors): ?>
        <p>Ahora mismo no hay ningún mentor oficial disponible. Vuelve a mirar más tarde.</p>
    <?php else: ?>
        <ul class="mentors">
            <?php foreach ($mentors as $mentor): ?>
                <li><?php require ROOT_DIR . '/src/views/mentor_card.php'; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p class="actions"><a href="available.php">Actualizar</a></p>
</section>
<?php render_footer(); ?>
