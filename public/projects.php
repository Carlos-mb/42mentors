<?php
// Caso de uso ESTUDIANTE: proyectos para los que hay mentores en su campus.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$myProjects = user_projects();

// Proyectos con al menos un mentor activo (distinto de mí) en mi campus
$stmt = db()->prepare(
    "SELECT p.id, p.name, COUNT(*) AS mentors
       FROM mentor_projects mp
       JOIN projects p ON p.id = mp.project_id
       JOIN users u    ON u.id = mp.user_id
      WHERE u.id <> ? AND u.campus_id <=> ? AND " . sql_mentor_visible() . "
      GROUP BY p.id, p.name"
);
$stmt->execute([$user['id'], $user['campus_id']]);

$projects = [];
foreach ($stmt->fetchAll() as $row) {
    $mine = $myProjects[(int) $row['id']] ?? null;
    if ($mine && $mine['validated']) {
        continue; // ya lo tengo validado: no necesito mentor
    }
    if ($mine && !empty($mine['piscine'])) {
        continue; // proyecto de la piscina: ya no se hace después de ella
    }
    $row['in_progress'] = $mine && $mine['status'] === 'in_progress';
    $projects[] = $row;
}
// Primero los que estoy haciendo, luego por nombre
usort($projects, fn(array $a, array $b): int =>
    [$b['in_progress'], strtolower($a['name'])] <=> [$a['in_progress'], strtolower($b['name'])]);

render_header('Proyectos', 'projects');
?>
<section class="card">
    <h1>Busca ayuda por proyecto</h1>
    <p class="muted">Proyectos que aún no has validado y para los que hay compañeros de tu campus dispuestos a ayudar.
        ¿Buscas a alguien en concreto? <a href="mentors.php">Ver todos los mentores</a>.</p>

    <?php if (!$projects): ?>
        <p>Todavía no hay mentores para proyectos que te falten. ¡Anima a tus compañeros a apuntarse en «Mi perfil»!</p>
    <?php else: ?>
        <input type="search" class="filter" placeholder="Filtrar proyectos…" data-filter="#project-list">
        <ul class="projects" id="project-list">
            <?php foreach ($projects as $project): ?>
                <li data-name="<?= e(strtolower($project['name'])) ?>">
                    <a href="project.php?id=<?= (int) $project['id'] ?>">
                        <span class="name"><?= e($project['name']) ?></span>
                        <?php if ($project['in_progress']): ?>
                            <span class="badge">En curso</span>
                        <?php endif; ?>
                        <span class="count"><?= (int) $project['mentors'] ?> mentor<?= $project['mentors'] == 1 ? '' : 'es' ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<script src="assets/filter.js"></script>
<?php render_footer(); ?>
