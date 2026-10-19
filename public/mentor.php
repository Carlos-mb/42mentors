<?php
// Ficha pública de un mentor: lo que escribe el mentor (BD) + datos de la intra (caché de perfiles, fuera de la BD).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$login = is_string($_GET['login'] ?? null) ? $_GET['login'] : '';
$validLogin = preg_match('/^[a-z0-9_-]{1,64}$/i', $login) === 1;

// Solo mentores de mi campus con algún proyecto o mentores oficiales (o yo mismo). Quien solo vota también está en users, pero no es mentor.
$mentor = false;
if ($validLogin) {
    $stmt = db()->prepare(
        'SELECT id, login, campus_id, bio, availability, contact_pref, languages, consented_at
           FROM users u
          WHERE login = ?
            AND (id = ? OR (campus_id <=> ? AND (EXISTS (SELECT 1 FROM mentor_projects mp WHERE mp.user_id = u.id)
                                                 OR ' . sql_is_official() . ')))'
    );
    $stmt->execute([$login, $user['id'], $user['campus_id']]);
    $mentor = $stmt->fetch();
}

if (!$mentor) {
    http_response_code(404);
    render_header(__('Mentor no encontrado'), 'mentors');
    ?>
    <section class="card">
        <h1><?= e(__('Esta persona no es mentor')) ?></h1>
        <p><?= e(__('No hay ningún mentor de tu campus con ese login.')) ?></p>
        <p class="actions">
            <a class="btn" href="mentors.php"><?= e(__('Ver mentores')) ?></a>
            <?php if ($validLogin): ?>
                <a href="<?= e(intra_profile_url($login)) ?>" target="_blank" rel="noopener"><?= e(__('Ver su perfil en la intra')) ?> ↗</a>
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

// «Me ayudó»: solo totales (anónimos) y, si no es mi ficha, mis propios votos
$votesOn = votes_enabled();
$voteTotals = $votesOn ? mentor_vote_totals($mentorId) : [];
$points = array_sum(array_column($voteTotals, 'points'));
$voteCount = array_sum(array_column($voteTotals, 'votes'));
$myVotes = $isMe || !$votesOn ? [] : my_votes_for($user['id'], $mentorId);

// Datos de la intra: caché de perfiles (2 peticiones si ha caducado) + puesto (caché de ubicaciones del campus)
$profile = [];
$host = null;
$apiOk = true;
try {
    $profile = cached_mentor_detail($mentorId, user_token());
    if ($mentor['campus_id'] !== null) {
        $host = active_locations((int) $mentor['campus_id'], user_token())[$mentorId] ?? null;
    }
} catch (FtApiException $ex) {
    if ($ex->getCode() === 401) {
        relogin();
    }
    $apiOk = false;
}

$name = ($profile['displayname'] ?? '') ?: $mentor['login'];
$image = profile_image($profile, 'large');
$cursus = $profile['cursus'] ?? null;
$coalitions = $profile['coalitions'] ?? [];
// Nota y fecha de validación de cada proyecto (según la intra)
$projectStats = $profile['projects'] ?? [];

$languages = array_map(fn(string $code): string => LANGUAGE_LABELS[$code], parse_languages($mentor['languages']));
// El usuario de Slack de 42 es siempre el login de la intra
$showSlack = in_array($mentor['contact_pref'], ['slack', 'both'], true);

render_header($name, 'mentors');
?>
<section class="card profile">
    <p><a href="mentors.php">← <?= e(__('Mentores')) ?></a></p>

    <div class="profile-head">
        <?php if ($image): ?>
            <img src="<?= e($image) ?>" alt="" referrerpolicy="no-referrer" class="avatar-lg" data-initial="<?= e(strtoupper(substr($mentor['login'], 0, 1))) ?>">
        <?php else: ?>
            <span class="avatar-lg placeholder"><?= e(strtoupper(substr($mentor['login'], 0, 1))) ?></span>
        <?php endif; ?>
        <div class="profile-id">
            <h1><?= e($name) ?></h1>
            <p class="muted">
                <?= e($mentor['login']) ?> ·
                <a href="<?= e(intra_profile_url($mentor['login'])) ?>" target="_blank" rel="noopener"><?= e(__('Ver en la intra')) ?> ↗</a>
            </p>
            <p class="chips">
                <?= availability_badge($mentor['availability'], true) ?>
                <?php if ($apiOk): ?>
                    <?php if ($host): ?>
                        <span class="status online"><?= e(__('En el cluster')) ?> · <?= e($host) ?></span>
                    <?php else: ?>
                        <span class="status"><?= e(__('No conectado')) ?></span>
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
            <a class="btn edit" href="profile.php"><?= e(__('Editar mi perfil')) ?></a>
        <?php endif; ?>
    </div>

    <?php if (!$apiOk): ?>
        <p class="warn"><?= e(__('No se ha podido consultar la intra ahora mismo: faltan foto, nivel y ubicación.')) ?></p>
    <?php endif; ?>
    <?php if ($mentor['availability'] === 'paused'): ?>
        <p class="warn"><?= e(__('Está en pausa: ahora mismo no aparece en las búsquedas.')) ?></p>
    <?php endif; ?>

    <?php if ($mentor['bio']): ?>
        <blockquote class="bio"><?= e($mentor['bio']) ?></blockquote>
    <?php endif; ?>

    <dl class="facts">
        <?php if ($cursus): ?>
            <dt><?= e(__('Nivel')) ?></dt>
            <dd><?= e(format_level($cursus['level'])) ?> · <?= e($cursus['name']) ?></dd>
        <?php endif; ?>
        <dt><?= e(__('Contacto')) ?></dt>
        <dd>
            <?= e(isset(CONTACT_LABELS[$mentor['contact_pref']]) ? __(CONTACT_LABELS[$mentor['contact_pref']]) : '') ?>
            <?php if ($showSlack): ?>
                · Slack: <strong>@<?= e($mentor['login']) ?></strong>
            <?php endif; ?>
        </dd>
        <?php if ($languages): ?>
            <dt><?= e(__('Idiomas')) ?></dt>
            <dd><?= e(implode(', ', $languages)) ?></dd>
        <?php endif; ?>
        <dt><?= e(__('Mentor desde')) ?></dt>
        <dd><?= e(month_year($mentor['consented_at'])) ?></dd>
        <?php if ($votesOn): ?>
            <dt>«<?= e(__('Me ayudó')) ?>»</dt>
            <dd><?= $voteCount ? e(points_text($points, $voteCount)) : '<span class="muted">' . e(__('Aún sin valoraciones')) . '</span>' ?></dd>
        <?php endif; ?>
    </dl>

    <h2><?= e(__('Proyectos que mentoriza')) ?></h2>
    <?php if (!$projects): ?>
        <p class="muted"><?= e(__('Todavía no ha elegido proyectos.')) ?></p>
    <?php else: ?>
        <ul class="mentored">
            <?php foreach ($projects as $project): ?>
                <?php
                $stats = $projectStats[(int) $project['id']] ?? [];
                $meta = [];
                if (isset($stats['final_mark'])) {
                    $meta[] = __('Nota {mark}', ['mark' => (int) $stats['final_mark']]);
                }
                $validatedOn = month_year($stats['marked_at'] ?? null);
                if ($validatedOn) {
                    $meta[] = __('validado en {date}', ['date' => $validatedOn]);
                }
                $projectPoints = $voteTotals[(int) $project['id']]['points'] ?? 0;
                if ($projectPoints) {
                    $meta[] = __('Me ayudó') . ': ' . __($projectPoints === 1 ? '{n} punto' : '{n} puntos', ['n' => $projectPoints]);
                }
                ?>
                <li id="project-<?= (int) $project['id'] ?>">
                    <a href="project.php?id=<?= (int) $project['id'] ?>"><?= e($project['name']) ?></a>
                    <?php if ($meta): ?>
                        <span class="muted small"> · <?= e(implode(' · ', $meta)) ?></span>
                    <?php endif; ?>
                    <?php if ($project['note']): ?>
                        <p class="note-text">«<?= e($project['note']) ?>»</p>
                    <?php endif; ?>
                    <?php if (!$isMe && $votesOn): ?>
                        <div class="vote-row">
                            <span class="muted small"><?= e(__('¿Te ayudó con este proyecto?')) ?></span>
                            <?= vote_form($mentorId, (int) $project['id'], $myVotes[(int) $project['id']] ?? null) ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
