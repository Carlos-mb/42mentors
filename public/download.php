<?php
// «Descargar mis datos»: todo lo que guardamos de la persona (acceso y portabilidad, arts. 15 y 20 del RGPD), en JSON.
// Solo por POST desde privacy.php. No incluye los datos de la intra (no se guardan) ni quién la ha valorado (anónimo).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('privacy.php');
}
csrf_check();

/** Fecha de la BD (UTC) en formato ISO 8601. */
function iso_utc(?string $date): ?string
{
    return $date === null ? null : str_replace(' ', 'T', $date) . 'Z';
}

$stmt = db()->prepare(
    'SELECT id, login, campus_id, bio, availability, contact_pref, languages, available_until,
            manguito_role, manguito_blocked, consented_at, consent_version, last_login_at
       FROM users WHERE id = ?'
);
$stmt->execute([$user['id']]);
$me = $stmt->fetch();
if (!$me) {
    flash(__('No tenemos ningún dato tuyo guardado.'));
    redirect('privacy.php');
}

// Proyectos que se ofrece a mentorizar
$stmt = db()->prepare(
    'SELECT p.id, p.name, mp.note FROM mentor_projects mp JOIN projects p ON p.id = mp.project_id
      WHERE mp.user_id = ? ORDER BY p.name'
);
$stmt->execute([$user['id']]);
$mentorProjects = $stmt->fetchAll();

// Valoraciones que ha hecho (a quién, por qué proyecto, puntos y fechas)
$stmt = db()->prepare(
    'SELECT u.login AS mentor_login, p.id AS project_id, p.name AS project, v.value AS points, v.created_at, v.updated_at
       FROM votes v JOIN users u ON u.id = v.mentor_id JOIN projects p ON p.id = v.project_id
      WHERE v.voter_id = ? ORDER BY v.created_at'
);
$stmt->execute([$user['id']]);
$votesGiven = array_map(fn(array $v): array => [
    'mentor_login' => $v['mentor_login'],
    'project_id'   => (int) $v['project_id'],
    'project'      => $v['project'],
    'points'       => (int) $v['points'],
    'created_at'   => iso_utc($v['created_at']),
    'updated_at'   => iso_utc($v['updated_at']),
], $stmt->fetchAll());

// Valoraciones recibidas: solo totales por proyecto, nunca quién votó ni cuándo (son anónimas)
$stmt = db()->prepare(
    'SELECT p.id, p.name, SUM(v.value) AS points, COUNT(*) AS votes
       FROM votes v JOIN projects p ON p.id = v.project_id
      WHERE v.mentor_id = ? GROUP BY p.id, p.name ORDER BY p.name'
);
$stmt->execute([$user['id']]);
$votesReceived = array_map(fn(array $v): array => [
    'project_id' => (int) $v['id'],
    'project'    => $v['name'],
    'points'     => (int) $v['points'],
    'votes'      => (int) $v['votes'],
], $stmt->fetchAll());

$data = [
    'about' => __('Datos que 42mentors guarda sobre ti. Las fechas están en UTC. No incluye los datos de la intra (nombre, foto, nivel…), que no se guardan, ni quién te ha valorado, porque esas valoraciones son anónimas.'),
    'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'account' => [
        'id'                   => (int) $me['id'],
        'login'                => $me['login'],
        'campus_id'            => $me['campus_id'] === null ? null : (int) $me['campus_id'],
        'consented_at'         => iso_utc($me['consented_at']),
        'accepted_policy_version' => (int) $me['consent_version'],
        'last_login_at'        => iso_utc($me['last_login_at']),
        'manguito_role'        => $me['manguito_role'],
        'manguito_blocked'     => (bool) $me['manguito_blocked'],
    ],
    'mentor_profile' => [
        'bio'             => $me['bio'],
        'availability'    => $me['availability'],
        'contact_pref'    => $me['contact_pref'],
        'languages'       => $me['languages'],
        'available_until' => iso_utc($me['available_until']),
    ],
    'mentor_projects'      => array_map(fn(array $p): array => ['project_id' => (int) $p['id'], 'project' => $p['name'], 'note' => $p['note']], $mentorProjects),
    'votes_given'          => $votesGiven,
    'votes_received_total' => $votesReceived,
];

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="42mentors-' . __('mis-datos') . '-' . $me['login'] . '-' . gmdate('Y-m-d') . '.json"');
header('Cache-Control: no-store');
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
