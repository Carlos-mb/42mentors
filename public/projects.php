<?php
// Caso de uso ESTUDIANTE: proyectos de su camino (su cursus) para los que hay mentores en su campus.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$myProjects = user_projects();

$showPast = ($_GET['past'] ?? '') === '1';
$path = $user['cursus_ids'] ?? []; // sesiones antiguas: sin cursus, no se filtra por camino

// Proyectos con al menos un mentor activo (distinto de mí) en mi campus, sin exámenes,
// con los cursus a los que se sabe que pertenecen (project_cursus)
$stmt = db()->prepare(
    "SELECT p.id, p.name, COUNT(DISTINCT mp.user_id) AS mentors, GROUP_CONCAT(DISTINCT pc.cursus_id) AS cursus
       FROM mentor_projects mp
       JOIN projects p ON p.id = mp.project_id
       JOIN users u    ON u.id = mp.user_id
       LEFT JOIN project_cursus pc ON pc.project_id = p.id
      WHERE u.id <> ? AND u.campus_id <=> ? AND p.name NOT LIKE 'Exam %' AND " . sql_mentor_visible() . "
      GROUP BY p.id, p.name"
);
$stmt->execute([$user['id'], $user['campus_id']]);

// Algunos proyectos tienen dos versiones con distinto id (Born2beroot…): si hice una, la otra cuenta como mía.
// Por nombre, quedándose con la versión validada si hay varias.
$myByName = [];
foreach ($myProjects as $mine) {
    $key = strtolower($mine['name']);
    if (!isset($myByName[$key]) || $mine['validated']) {
        $myByName[$key] = $mine;
    }
}

// Grupo: 0 = lo estoy haciendo, 1 = me queda por hacer, 2 = ya validado (solo con ?past=1)
$projects = [];
foreach ($stmt->fetchAll() as $row) {
    $mine = $myProjects[(int) $row['id']] ?? $myByName[strtolower($row['name'])] ?? null;
    if ($mine && !empty($mine['piscine'])) {
        continue; // proyecto de la piscina: ya no se hace después de ella
    }
    $cursus = $row['cursus'] !== null ? array_map('intval', explode(',', $row['cursus'])) : [];
    if ($path && $cursus && !array_intersect($path, $cursus)) {
        continue; // no es de mi camino (otro cursus)
    }
    $row['group'] = !$mine ? 1 : ($mine['validated'] ? 2 : 0);
    if ($row['group'] === 2 && !$showPast) {
        continue; // ya lo tengo validado: solo si pido verlos
    }
    $projects[] = $row;
}
// Primero los que estoy haciendo, luego los que me quedan y luego los validados; dentro, por nombre
usort($projects, fn(array $a, array $b): int =>
    [$a['group'], strtolower($a['name'])] <=> [$b['group'], strtolower($b['name'])]);

render_header(__('Proyectos'), 'projects');
?>
<section class="card">
    <h1><?= e(__('Busca ayuda por proyecto')) ?></h1>
    <p class="muted"><?= e(__('Proyectos de tu camino que estás haciendo o que te quedan por hacer, con compañeros de tu campus dispuestos a ayudar.')) ?>
        <?= e(__('¿Buscas a alguien en concreto?')) ?> <a href="mentors.php"><?= e(__('Ver todos los mentores')) ?></a>.</p>

    <?php if (!$projects): ?>
        <p><?= e(__('Todavía no hay mentores para proyectos que te falten. ¡Anima a tus compañeros a apuntarse en «Mi perfil»!')) ?></p>
    <?php else: ?>
        <input type="search" class="filter" placeholder="<?= e(__('Filtrar proyectos…')) ?>" data-filter="#project-list">
        <ul class="projects" id="project-list">
            <?php foreach ($projects as $project): ?>
                <li data-name="<?= e(strtolower($project['name'])) ?>">
                    <a href="project.php?id=<?= (int) $project['id'] ?>">
                        <span class="name"><?= e($project['name']) ?></span>
                        <?php if ($project['group'] === 0): ?>
                            <span class="badge"><?= e(__('En curso')) ?></span>
                        <?php elseif ($project['group'] === 2): ?>
                            <span class="badge"><?= e(__('Validado')) ?></span>
                        <?php endif; ?>
                        <span class="count"><?= e(__($project['mentors'] == 1 ? '{n} mentor' : '{n} mentores', ['n' => (int) $project['mentors']])) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p><?php if ($showPast): ?>
        <a href="projects.php"><?= e(__('Ocultar los que ya has validado')) ?></a>
    <?php else: ?>
        <a href="projects.php?past=1"><?= e(__('Ver también los que ya has validado')) ?></a>
    <?php endif; ?></p>
    <p class="muted"><?= e(__('La lista se deduce de los datos que envía la API de 42, así que puede tener algún error (por ejemplo, con proyectos que tienen dos versiones).')) ?></p>
</section>
<script src="assets/filter.js"></script>
<?php render_footer(); ?>
