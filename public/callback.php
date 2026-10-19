<?php
// Paso 2 del OAuth: la intra vuelve aquí con ?code=...&state=...
require __DIR__ . '/../src/bootstrap.php';

if (isset($_GET['error'])) {
    render_error('Has cancelado el acceso o la intra ha devuelto un error: ' . (string) $_GET['error'], 400);
}

$expectedState = $_SESSION['oauth_state'] ?? '';
unset($_SESSION['oauth_state']);
if ($expectedState === '' || !hash_equals($expectedState, (string) ($_GET['state'] ?? ''))) {
    // La vuelta desde la intra a veces llega dos veces: si ya hay sesión, el login ya se hizo
    if (current_user() !== null) {
        header('Location: index.php');
        exit;
    }
    render_error('La petición de login no es válida (state incorrecto). Vuelve a intentarlo.', 400);
}

$code = (string) ($_GET['code'] ?? '');
if ($code === '') {
    render_error('Falta el código de autorización.', 400);
}

try {
    $token = ft_exchange_code($code);
    $me = ft_get('/v2/me', $token['access_token']);
} catch (FtApiException $ex) {
    render_error('No se pudo completar el login con la intra: ' . $ex->getMessage(), 502);
}

// Campus principal del usuario
$campusId = null;
foreach ($me['campus_users'] ?? [] as $campusUser) {
    if (!empty($campusUser['is_primary'])) {
        $campusId = (int) $campusUser['campus_id'];
    }
}
if ($campusId === null && isset($me['campus'][0]['id'])) {
    $campusId = (int) $me['campus'][0]['id'];
}

// Zona horaria del campus (para escribir y mostrar horas); solo en la sesión
$timeZone = null;
foreach ($me['campus'] ?? [] as $campus) {
    if ((int) ($campus['id'] ?? 0) === $campusId && in_array($campus['time_zone'] ?? '', timezone_identifiers_list(), true)) {
        $timeZone = $campus['time_zone'];
    }
}

// Cursus de piscina del usuario (C Piscine, C-Piscine-Reloaded…): su slug contiene «piscine»
$piscineCursus = [];
foreach ($me['cursus_users'] ?? [] as $cursusUser) {
    if (str_contains((string) ($cursusUser['cursus']['slug'] ?? ''), 'piscine')) {
        $piscineCursus[(int) $cursusUser['cursus']['id']] = true;
    }
}

// Proyectos del usuario (solo proyectos principales, sin subproyectos)
$projects = [];
foreach ($me['projects_users'] ?? [] as $projectUser) {
    $project = $projectUser['project'] ?? null;
    if (!$project || !empty($project['parent_id'])) {
        continue;
    }
    // De piscina = todos sus cursus son de piscina (después de la piscina no tiene sentido mentorizarlos)
    $cursusIds = array_map('intval', $projectUser['cursus_ids'] ?? []);
    $piscine = $cursusIds && !array_diff_key(array_flip($cursusIds), $piscineCursus);
    $projects[(int) $project['id']] = [
        'id'        => (int) $project['id'],
        'name'      => (string) $project['name'],
        'slug'      => (string) $project['slug'],
        'status'    => (string) ($projectUser['status'] ?? ''),
        'validated' => ($projectUser['validated?'] ?? null) === true,
        'piscine'   => $piscine,
    ];
}

// Solo en la sesión (no se guarda en BD): nombre y foto para la cabecera
$user = [
    'id'          => (int) $me['id'],
    'login'       => (string) $me['login'],
    'displayname' => (string) ($me['displayname'] ?? ''),
    'image_url'   => $me['image']['versions']['small'] ?? $me['image']['link'] ?? null,
    'campus_id'   => $campusId,
    'time_zone'   => $timeZone,
];

// Si ya dio su consentimiento, actualizar sus datos mínimos. Si no, NO se guarda nada.
$stmt = db()->prepare('UPDATE users SET login = ?, campus_id = ?, last_login_at = NOW() WHERE id = ?');
$stmt->execute([$user['login'], $user['campus_id'], $user['id']]);
$user['consented'] = user_has_consented($user['id']);
// Borra de la BD a quien lleva más de USER_RETENTION_MONTHS sin entrar (como mucho una vez al día)
try {
    purge_inactive_users();
} catch (Throwable $e) {
    error_log('purge_inactive_users: ' . $e->getMessage());
}
// Rol de la Operación Manguito (mentor oficial, manguito o ninguno): solo en la sesión
$user['role'] = user_role($user['id']);

session_regenerate_id(true);
$_SESSION['user'] = $user;
$_SESSION['projects'] = $projects;
$_SESSION['access_token'] = $token['access_token'];
$_SESSION['token_expires_at'] = time() + (int) ($token['expires_in'] ?? 7200);

// Si venía de una página privada de declaración (mentor-manguito.php, soy-manguito.php), vuelve a ella
$return = $_SESSION['login_return'] ?? null;
unset($_SESSION['login_return']);
$return = in_array($return, JOIN_PAGES, true) ? $return : 'index.php';
// Quien ya tiene datos guardados pero aceptó una versión anterior de la política tiene que aceptar la nueva ya
if (!$user['consented'] && user_has_outdated_consent($user['id'])) {
    redirect('consent.php?next=' . rawurlencode($return));
}
redirect($return);
