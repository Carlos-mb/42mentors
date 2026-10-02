<?php
// Mi perfil de mentor: proyectos que mentorizo y datos de mi ficha pública (mentor.php).
require __DIR__ . '/../src/bootstrap.php';

$user = require_consent();

// Proyectos terminados y validados (datos de /v2/me tomados al iniciar sesión)
$finished = array_filter(
    user_projects(),
    fn(array $p): bool => $p['validated'] && $p['status'] === 'finished'
);
uasort($finished, fn(array $a, array $b): int => strcasecmp($a['name'], $b['name']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $bio = clean_text(is_string($_POST['bio'] ?? null) ? $_POST['bio'] : '', 160);
    $availability = is_string($_POST['availability'] ?? null) ? $_POST['availability'] : '';
    if (!isset(AVAILABILITY_LABELS[$availability])) {
        $availability = 'available';
    }
    $contact = is_string($_POST['contact_pref'] ?? null) ? $_POST['contact_pref'] : '';
    if (!isset(CONTACT_LABELS[$contact])) {
        $contact = 'cluster';
    }
    $postedLanguages = array_filter((array) ($_POST['languages'] ?? []), 'is_string');
    $languages = implode(',', array_intersect(array_keys(LANGUAGE_LABELS), $postedLanguages));

    // Solo se aceptan proyectos que el usuario ha validado de verdad
    $selected = array_map('intval', array_filter((array) ($_POST['projects'] ?? []), 'is_scalar'));
    $selected = array_values(array_intersect($selected, array_keys($finished)));
    $notes = (array) ($_POST['notes'] ?? []);

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'UPDATE users SET bio = ?, availability = ?, contact_pref = ?, languages = ? WHERE id = ?'
        )->execute([
            $bio !== '' ? $bio : null,
            $availability,
            $contact,
            $languages !== '' ? $languages : null,
            $user['id'],
        ]);

        $upsertProject = $pdo->prepare(
            'INSERT INTO projects (id, name, slug) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug)'
        );
        $insertMentor = $pdo->prepare('INSERT INTO mentor_projects (user_id, project_id, note) VALUES (?, ?, ?)');

        $pdo->prepare('DELETE FROM mentor_projects WHERE user_id = ?')->execute([$user['id']]);
        foreach ($selected as $projectId) {
            $project = $finished[$projectId];
            $note = clean_text(is_string($notes[$projectId] ?? null) ? $notes[$projectId] : '', 160);
            $upsertProject->execute([$project['id'], $project['name'], $project['slug']]);
            $insertMentor->execute([$user['id'], $project['id'], $note !== '' ? $note : null]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }

    $_SESSION['profile_saved'] = true; // se muestra la confirmación una vez, tras la redirección
    redirect('profile.php');
}

$justSaved = !empty($_SESSION['profile_saved']);
unset($_SESSION['profile_saved']);

$stmt = db()->prepare('SELECT bio, availability, contact_pref, languages FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$me = $stmt->fetch();
if (!$me) {
    // Sus datos se borraron (p. ej. desde otro dispositivo): pedir de nuevo el consentimiento
    $_SESSION['user']['consented'] = false;
    redirect('consent.php');
}
$myLanguages = parse_languages($me['languages']);

$stmt = db()->prepare('SELECT project_id, note FROM mentor_projects WHERE user_id = ?');
$stmt->execute([$user['id']]);
$marked = [];
foreach ($stmt->fetchAll() as $row) {
    $marked[(int) $row['project_id']] = (string) ($row['note'] ?? '');
}

render_header('Mi perfil', 'profile');
?>
<?php if ($justSaved): ?>
    <dialog id="saved-dialog" class="saved-dialog">
        <p class="saved-icon" aria-hidden="true">✓</p>
        <h2>Cambios guardados</h2>
        <p class="muted">Tu ficha de mentor ya está actualizada.</p>
        <div class="actions">
            <a class="btn" href="mentor.php?login=<?= e(rawurlencode($user['login'])) ?>">Ver mi ficha pública</a>
            <form method="dialog"><button type="submit" class="btn btn-secondary">Seguir editando</button></form>
        </div>
    </dialog>
    <noscript><div class="flash">Cambios guardados.</div></noscript>
<?php endif; ?>
<form method="post" class="stack" id="profile-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <section class="card">
        <div class="card-head">
            <h1>Mi perfil de mentor</h1>
            <a href="mentor.php?login=<?= e(rawurlencode($user['login'])) ?>">Ver mi ficha pública →</a>
        </div>
        <p class="muted">Tu nombre, foto, nivel, coalición y puesto en el cluster salen de la intra. Aquí eliges el resto.</p>

        <label class="field">
            <span>Presentación <span class="muted small">(opcional, máx. 160 caracteres)</span></span>
            <textarea name="bio" maxlength="160" rows="2"
                      placeholder="Ej.: Pregúntame por pipes, señales y el bonus de minishell"><?= e($me['bio']) ?></textarea>
        </label>

        <fieldset class="field">
            <legend>Disponibilidad</legend>
            <?php foreach (AVAILABILITY_LABELS as $value => $label): ?>
                <label class="radio">
                    <input type="radio" name="availability" value="<?= e($value) ?>"
                        <?= $me['availability'] === $value ? 'checked' : '' ?>>
                    <?= e($label) ?>
                </label>
            <?php endforeach; ?>
            <p class="muted small">«En pausa»: no apareces en las búsquedas, pero no se borra nada (útil en época de exámenes).</p>
        </fieldset>

        <fieldset class="field">
            <legend>Cómo prefieres que te contacten</legend>
            <?php foreach (CONTACT_LABELS as $value => $label): ?>
                <label class="radio">
                    <input type="radio" name="contact_pref" value="<?= e($value) ?>"
                        <?= $me['contact_pref'] === $value ? 'checked' : '' ?>>
                    <?= e($label) ?>
                </label>
            <?php endforeach; ?>
            <p class="muted small">Si eliges Slack, en tu ficha aparecerá <strong>@<?= e($user['login']) ?></strong> (tu usuario de Slack es tu login de 42).</p>
        </fieldset>

        <fieldset class="field">
            <legend>Idiomas en los que puedes ayudar</legend>
            <?php foreach (LANGUAGE_LABELS as $code => $label): ?>
                <label class="check">
                    <input type="checkbox" name="languages[]" value="<?= e($code) ?>"
                        <?= in_array($code, $myLanguages, true) ? 'checked' : '' ?>>
                    <?= e($label) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
    </section>

    <section class="card">
        <h2>Proyectos que puedo mentorizar</h2>
        <p class="muted">Tus proyectos terminados y validados. Marca con cuáles puedes ayudar y, si quieres, añade una nota.</p>
        <?php if (!$finished): ?>
            <p>Aún no tienes proyectos validados. ¡Vuelve cuando termines el primero!</p>
        <?php else: ?>
            <input type="search" class="filter" placeholder="Filtrar proyectos…" data-filter="#mentor-list">
            <ul class="checklist" id="mentor-list">
                <?php foreach ($finished as $project): $isMarked = isset($marked[$project['id']]); ?>
                    <li data-name="<?= e(strtolower($project['name'])) ?>">
                        <label>
                            <input type="checkbox" name="projects[]" value="<?= (int) $project['id'] ?>"
                                <?= $isMarked ? 'checked' : '' ?>>
                            <?= e($project['name']) ?>
                        </label>
                        <input type="text" class="note" name="notes[<?= (int) $project['id'] ?>]" maxlength="160"
                               value="<?= e($marked[$project['id']] ?? '') ?>"
                               placeholder="Nota opcional (p. ej.: solo la parte obligatoria)"
                               <?= $isMarked ? '' : 'hidden' ?>>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <p class="muted small">La lista de proyectos se actualiza cada vez que inicias sesión.</p>
    </section>

    <div class="actions">
        <button type="submit" class="btn">Guardar cambios</button>
    </div>
</form>
<script src="assets/filter.js"></script>
<script src="assets/profile.js"></script>
<?php render_footer(); ?>
