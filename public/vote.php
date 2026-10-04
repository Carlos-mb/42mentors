<?php
// Guarda una valoración «Me ayudó» (1 a 3) a un mentor por un proyecto. Solo por POST, desde mentor.php o ratings.php.
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('ratings.php');
}
csrf_check();

$mentorId = filter_input(INPUT_POST, 'mentor_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3]]);
$toRatings = ($_POST['back'] ?? '') === 'ratings';

// Solo a mentores de mi campus, por un proyecto que mentorizan, y nunca a uno mismo
$mentorLogin = false;
if ($mentorId && $projectId && $value) {
    $stmt = db()->prepare(
        'SELECT u.login
           FROM users u
           JOIN mentor_projects mp ON mp.user_id = u.id AND mp.project_id = ?
          WHERE u.id = ? AND u.id <> ? AND u.campus_id <=> ?'
    );
    $stmt->execute([$projectId, $mentorId, $user['id'], $user['campus_id']]);
    $mentorLogin = $stmt->fetchColumn();
}
if ($mentorLogin === false) {
    flash('No se ha podido guardar la valoración: ese mentor ya no ofrece ayuda con ese proyecto.');
    redirect($toRatings ? 'ratings.php' : 'mentors.php');
}

$mentorPage = 'mentor.php?login=' . rawurlencode($mentorLogin);

// Guardar quién vota exige su consentimiento (se mira en la BD: pudo borrar sus datos desde otro dispositivo)
if (!user_has_consented($user['id'])) {
    $_SESSION['user']['consented'] = false;
    flash('Para guardar tu valoración necesitamos antes tu permiso. Después, vuelve a pulsarla.');
    redirect('consent.php?next=' . rawurlencode($toRatings ? 'ratings.php' : $mentorPage));
}

// Al cambiar el voto, created_at se mantiene: el ranking cuenta por la fecha del primer voto
db()->prepare(
    'INSERT INTO votes (voter_id, mentor_id, project_id, value, created_at, updated_at)
     VALUES (?, ?, ?, ?, NOW(), NOW())
     ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()'
)->execute([$user['id'], $mentorId, $projectId, $value]);

flash('Valoración guardada. ¡Gracias!');
redirect($toRatings ? 'ratings.php#vote-' . $mentorId . '-' . $projectId : $mentorPage . '#project-' . $projectId);
