<?php
// Mentores de un proyecto, con su ubicación en el cluster si están conectados. Cada uno enlaza a su ficha.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$projectId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT id, name, slug FROM projects WHERE id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) {
    render_error('Ese proyecto no existe o no tiene mentores.', 404);
}

$stmt = db()->prepare(
    "SELECT u.id, u.login, u.availability, mp.note
       FROM mentor_projects mp
       JOIN users u ON u.id = mp.user_id
      WHERE mp.project_id = ? AND u.id <> ? AND u.campus_id <=> ? AND u.availability <> 'paused'"
);
$stmt->execute([$projectId, $user['id'], $user['campus_id']]);
$mentors = $stmt->fetchAll();

$apiOk = enrich_mentors($mentors, $user['campus_id']);
sort_mentors($mentors);

render_header($project['name'], 'projects');
?>
<section class="card">
    <p><a href="projects.php">← Volver a proyectos</a></p>
    <h1><?= e($project['name']) ?></h1>
    <?php if (!$apiOk): ?>
        <p class="warn">No se ha podido consultar la intra ahora mismo: faltan nombres, fotos o ubicaciones.</p>
    <?php endif; ?>

    <?php if (!$mentors): ?>
        <p>Ahora mismo no hay mentores de tu campus para este proyecto.</p>
    <?php else: ?>
        <ul class="mentors">
            <?php foreach ($mentors as $mentor): ?>
                <li><?php require ROOT_DIR . '/src/views/mentor_card.php'; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
