<?php
// Directorio de mentores del campus. El buscador filtra en el navegador (mentors.js):
// se carga la lista completa con nombre y foto (una petición a la API por cada 100 mentores).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();

// Mentores de mi campus (yo incluido) con al menos un proyecto y que no están en pausa
$stmt = db()->prepare(
    "SELECT u.id, u.login, u.availability, u.bio
       FROM users u
      WHERE u.campus_id <=> ? AND u.availability <> 'paused'
        AND EXISTS (SELECT 1 FROM mentor_projects mp WHERE mp.user_id = u.id)"
);
$stmt->execute([$user['campus_id']]);
$mentors = $stmt->fetchAll();

// Proyectos de cada mentor (para las etiquetas y el filtro por proyecto)
$projectsByMentor = [];
$allProjects = [];
if ($mentors) {
    $stmt = db()->prepare(
        "SELECT mp.user_id, p.id, p.name
           FROM mentor_projects mp
           JOIN projects p ON p.id = mp.project_id
           JOIN users u    ON u.id = mp.user_id
          WHERE u.campus_id <=> ? AND u.availability <> 'paused'
          ORDER BY p.name"
    );
    $stmt->execute([$user['campus_id']]);
    foreach ($stmt->fetchAll() as $row) {
        $projectsByMentor[(int) $row['user_id']][(int) $row['id']] = $row['name'];
        $allProjects[(int) $row['id']] = $row['name'];
    }
}

$apiOk = enrich_mentors($mentors, $user['campus_id']);
foreach ($mentors as &$mentor) {
    $mentor['projects'] = $projectsByMentor[(int) $mentor['id']] ?? [];
    $mentor['is_me'] = (int) $mentor['id'] === $user['id'];
}
unset($mentor);
sort_mentors($mentors);

render_header('Mentores', 'mentors');
?>
<section class="card">
    <h1>Mentores de tu campus</h1>
    <p class="muted">Compañeros que se ofrecen a ayudar. Busca por nombre o login, o filtra por proyecto.</p>
    <?php if (!$apiOk): ?>
        <p class="warn">No se ha podido consultar la intra ahora mismo: faltan nombres, fotos o ubicaciones.</p>
    <?php endif; ?>

    <?php if (!$mentors): ?>
        <p>Todavía no hay mentores en tu campus. <a href="profile.php">¿Quieres ser el primero?</a></p>
    <?php else: ?>
        <div class="filters">
            <input type="search" id="q" class="filter" placeholder="Buscar por nombre o login…" autocomplete="off">
            <select id="project" aria-label="Filtrar por proyecto">
                <option value="">Todos los proyectos</option>
                <?php foreach ($allProjects as $projectId => $projectName): ?>
                    <option value="<?= (int) $projectId ?>"><?= e($projectName) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="check"><input type="checkbox" id="online"> Solo conectados ahora</label>
        </div>
        <p class="muted small" id="mentor-count"><?= count($mentors) ?> mentores</p>

        <ul class="mentors" id="mentor-list">
            <?php foreach ($mentors as $mentor): ?>
                <li data-search="<?= e($mentor['login'] . ' ' . ($mentor['displayname'] ?? '')) ?>"
                    data-projects=",<?= e(implode(',', array_keys($mentor['projects']))) ?>,"
                    data-online="<?= $mentor['host'] ? '1' : '0' ?>">
                    <?php require ROOT_DIR . '/src/views/mentor_card.php'; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="muted" id="mentor-empty" hidden>Ningún mentor coincide con la búsqueda.</p>
    <?php endif; ?>
</section>
<script src="assets/mentors.js"></script>
<?php render_footer(); ?>
