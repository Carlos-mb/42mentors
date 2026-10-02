<?php
// Ficha pública de un mentor: lo que escribe el mentor (BD) + datos de la intra (API, en el momento, no se guardan).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$login = is_string($_GET['login'] ?? null) ? $_GET['login'] : '';
$validLogin = preg_match('/^[a-z0-9_-]{1,64}$/i', $login) === 1;

// Solo mentores de mi campus (o yo mismo)
$mentor = false;
if ($validLogin) {
    $stmt = db()->prepare(
        'SELECT id, login, bio, availability, contact_pref, languages, consented_at
           FROM users
          WHERE login = ? AND (campus_id <=> ? OR id = ?)'
    );
    $stmt->execute([$login, $user['campus_id'], $user['id']]);
    $mentor = $stmt->fetch();
}

if (!$mentor) {
    http_response_code(404);
    render_header('Mentor no encontrado', 'mentors');
    ?>
    <section class="card">
        <h1>Esta persona no es mentor</h1>
        <p>No hay ningún mentor de tu campus con ese login.</p>
        <p class="actions">
            <a class="btn" href="mentors.php">Ver mentores</a>
            <?php if ($validLogin): ?>
                <a href="<?= e(intra_profile_url($login)) ?>" target="_blank" rel="noopener">Ver su perfil en la intra ↗</a>
            <?php endif; ?>
        </p>
    </section>
    <?php
    render_footer();
    exit;
}

$mentorId = (int) $mentor['id'];
$isMe = $mentorId === $user['id'];

$stmt = db()->prepare(
    'SELECT p.id, p.name, mp.note
       FROM mentor_projects mp
       JOIN projects p ON p.id = mp.project_id
      WHERE mp.user_id = ?
      ORDER BY p.name'
);
$stmt->execute([$mentorId]);
$projects = $stmt->fetchAll();

// Datos de la intra: 2 peticiones (perfil + coalición)
$profile = [];
$coalitions = [];
$apiOk = true;
try {
    $profile = ft_get('/v2/users/' . $mentorId, user_token());
    $coalitions = ft_get('/v2/users/' . $mentorId . '/coalitions', user_token());
} catch (FtApiException $ex) {
    if ($ex->getCode() === 401) {
        relogin();
    }
    $apiOk = false;
}

$name = ($profile['displayname'] ?? '') ?: $mentor['login'];
$image = profile_image($profile, 'large');
$cursus = main_cursus($profile);
$host = $profile['location'] ?? null;

// Nota y fecha de validación de cada proyecto (según la intra)
$projectStats = [];
foreach ($profile['projects_users'] ?? [] as $projectUser) {
    $projectStats[(int) ($projectUser['project']['id'] ?? 0)] = $projectUser;
}

$languages = array_map(fn(string $code): string => LANGUAGE_LABELS[$code], parse_languages($mentor['languages']));
// El usuario de Slack de 42 es siempre el login de la intra
$showSlack = in_array($mentor['contact_pref'], ['slack', 'both'], true);

render_header($name, 'mentors');
?>
<section class="card profile">
    <p><a href="mentors.php">← Mentores</a></p>

    <div class="profile-head">
        <?php if ($image): ?>
            <img src="<?= e($image) ?>" alt="" class="avatar-lg">
        <?php else: ?>
            <span class="avatar-lg placeholder"><?= e(strtoupper(substr($mentor['login'], 0, 1))) ?></span>
        <?php endif; ?>
        <div class="profile-id">
            <h1><?= e($name) ?></h1>
            <p class="muted">
                <?= e($mentor['login']) ?> ·
                <a href="<?= e(intra_profile_url($mentor['login'])) ?>" target="_blank" rel="noopener">Ver en la intra ↗</a>
            </p>
            <p class="chips">
                <?= availability_badge($mentor['availability'], true) ?>
                <?php if ($apiOk): ?>
                    <?php if ($host): ?>
                        <span class="status online">En el cluster · <?= e($host) ?></span>
                    <?php else: ?>
                        <span class="status">No conectado</span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php foreach ($coalitions as $coalition): ?>
                    <?php $color = safe_color($coalition['color'] ?? null); ?>
                    <span class="chip chip-color"<?= $color ? ' style="background: ' . e($color) . '"' : '' ?>>
                        <?= e($coalition['name'] ?? '') ?>
                    </span>
                <?php endforeach; ?>
            </p>
        </div>
        <?php if ($isMe): ?>
            <a class="btn edit" href="profile.php">Editar mi perfil</a>
        <?php endif; ?>
    </div>

    <?php if (!$apiOk): ?>
        <p class="warn">No se ha podido consultar la intra ahora mismo: faltan foto, nivel y ubicación.</p>
    <?php endif; ?>
    <?php if ($mentor['availability'] === 'paused'): ?>
        <p class="warn">Está en pausa: ahora mismo no aparece en las búsquedas.</p>
    <?php endif; ?>

    <?php if ($mentor['bio']): ?>
        <blockquote class="bio"><?= e($mentor['bio']) ?></blockquote>
    <?php endif; ?>

    <dl class="facts">
        <?php if ($cursus): ?>
            <dt>Nivel</dt>
            <dd><?= e(format_level($cursus['level'] ?? 0)) ?> · <?= e($cursus['cursus']['name'] ?? '') ?></dd>
        <?php endif; ?>
        <dt>Contacto</dt>
        <dd>
            <?= e(CONTACT_LABELS[$mentor['contact_pref']] ?? '') ?>
            <?php if ($showSlack): ?>
                · Slack: <strong>@<?= e($mentor['login']) ?></strong>
            <?php endif; ?>
        </dd>
        <?php if ($languages): ?>
            <dt>Idiomas</dt>
            <dd><?= e(implode(', ', $languages)) ?></dd>
        <?php endif; ?>
        <dt>Mentor desde</dt>
        <dd><?= e(month_year($mentor['consented_at'])) ?></dd>
    </dl>

    <h2>Proyectos que mentoriza</h2>
    <?php if (!$projects): ?>
        <p class="muted">Todavía no ha elegido proyectos.</p>
    <?php else: ?>
        <ul class="mentored">
            <?php foreach ($projects as $project): ?>
                <?php
                $stats = $projectStats[(int) $project['id']] ?? [];
                $meta = [];
                if (isset($stats['final_mark'])) {
                    $meta[] = 'Nota ' . (int) $stats['final_mark'];
                }
                $validatedOn = month_year($stats['marked_at'] ?? null);
                if ($validatedOn) {
                    $meta[] = 'validado en ' . $validatedOn;
                }
                ?>
                <li>
                    <a href="project.php?id=<?= (int) $project['id'] ?>"><?= e($project['name']) ?></a>
                    <?php if ($meta): ?>
                        <span class="muted small"> · <?= e(implode(' · ', $meta)) ?></span>
                    <?php endif; ?>
                    <?php if ($project['note']): ?>
                        <p class="note-text">«<?= e($project['note']) ?>»</p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
