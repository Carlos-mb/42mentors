<?php
// Mentores oficiales y manguitos: la lista la gestiona el administrador (admin.php).
declare(strict_types=1);

const ROSTER_ROLE_LABELS = [
    'mentor'   => 'Mentor oficial',
    'manguito' => 'Manguito',
];

/** Horas que puede anunciar un mentor oficial que está disponible. */
const AVAILABLE_HOURS = [1, 2, 3, 4];

/** ¿Es administrador? Los logins están en ADMIN_LOGINS (.env), separados por comas. */
function is_admin(string $login): bool
{
    $admins = array_filter(array_map('trim', explode(',', strtolower(env('ADMIN_LOGINS', '')))));
    return in_array(strtolower($login), $admins, true);
}

/** MANGUITOS_ALL=True en .env: provisional, mientras no hay permiso de 42 Madrid, todo el mundo cuenta como manguito. */
function manguitos_all(): bool
{
    return in_array(strtolower(env('MANGUITOS_ALL', '')), ['true', '1', 'yes', 'on'], true);
}

/** Rol de un usuario al iniciar sesión: el de la lista del administrador o, con MANGUITOS_ALL, manguito. */
function user_role(string $login): ?string
{
    return roster_role($login) ?? (manguitos_all() ? 'manguito' : null);
}

/** Rol de un login en la lista del administrador: 'mentor', 'manguito' o null. */
function roster_role(string $login): ?string
{
    $stmt = db()->prepare('SELECT role FROM roster WHERE login = ?');
    $stmt->execute([$login]);
    $role = $stmt->fetchColumn();
    return $role === false ? null : (string) $role;
}

/** ¿Puede ver «Disponibles ahora»? Manguitos, mentores oficiales y administradores. */
function can_see_available(array $user): bool
{
    return !empty($user['role']) || manguitos_all() || is_admin($user['login']);
}

/** Exige ser administrador. */
function require_admin(): array
{
    $user = require_login();
    if (!is_admin($user['login'])) {
        render_error('Esta página es solo para administradores.', 403);
    }
    return $user;
}

/** Condición SQL: usuarios de $alias que son mentores oficiales (por login). */
function sql_is_official(string $alias = 'u'): string
{
    return "EXISTS (SELECT 1 FROM roster r WHERE r.login = $alias.login AND r.role = 'mentor')";
}
