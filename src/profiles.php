<?php
// Caché de perfiles de mentores (nombre, foto, nivel, coalición…) para no gastar el límite de la API
// (2 peticiones/segundo y 1200/hora por app).
//
// Un fichero por mentor en cache/profiles/<id>.json con dos partes:
//   basic:  nombre y foto (listas de Proyectos y Mentores);
//   detail: además nivel, coalición y nota/fecha de los proyectos (ficha del mentor).
// Solo se guardan los campos que muestra la web, y solo de usuarios con consentimiento (tabla users).
// Caducan a los PROFILES_CACHE_TTL segundos; si la API falla, se usa la copia caducada.
// El puesto en el cluster NO se guarda aquí: cambia cada minuto y sale de active_locations().
declare(strict_types=1);

function profile_cache_file(int $userId): string
{
    return ROOT_DIR . '/cache/profiles/' . $userId . '.json';
}

function profile_cache_read(int $userId): array
{
    $file = profile_cache_file($userId);
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function profile_cache_write(int $userId, array $data): void
{
    $file = profile_cache_file($userId);
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    file_put_contents($file, json_encode($data), LOCK_EX);
}

/** ¿Sigue vigente una parte de la caché (basic o detail)? */
function profile_cache_fresh(array $part): bool
{
    $ttl = (int) env('PROFILES_CACHE_TTL', '3600');
    return isset($part['fetched_at']) && $part['fetched_at'] > time() - $ttl;
}

/** Borra la caché de un usuario («Borrar mis datos», usuarios inactivos…). */
function forget_profile(int $userId): void
{
    $file = profile_cache_file($userId);
    if (is_file($file)) {
        unlink($file);
    }
}

/** Nombre y foto de un perfil de la API, en el mismo formato (profile_image() sigue funcionando). */
function slim_basic(array $profile): array
{
    return [
        'displayname' => $profile['displayname'] ?? null,
        'image'       => [
            'link'     => $profile['image']['link'] ?? null,
            'versions' => array_intersect_key($profile['image']['versions'] ?? [], ['medium' => 1, 'large' => 1]),
        ],
    ];
}

/** Datos de la ficha: nombre, foto, cursus principal, nota/fecha por proyecto y coaliciones. */
function slim_detail(array $profile, array $coalitions): array
{
    $cursus = main_cursus($profile);
    $projects = [];
    foreach ($profile['projects_users'] ?? [] as $projectUser) {
        if (isset($projectUser['project']['id'])) {
            $projects[(int) $projectUser['project']['id']] = [
                'final_mark' => $projectUser['final_mark'] ?? null,
                'marked_at'  => $projectUser['marked_at'] ?? null,
            ];
        }
    }
    return slim_basic($profile) + [
        'cursus'     => $cursus ? ['level' => $cursus['level'] ?? 0, 'name' => $cursus['cursus']['name'] ?? ''] : null,
        'projects'   => $projects,
        'coalitions' => array_map(fn(array $coalition): array => [
            'name'  => $coalition['name'] ?? '',
            'color' => $coalition['color'] ?? null,
        ], $coalitions),
    ];
}

/**
 * Nombre y foto de varios mentores: [id => perfil reducido].
 * Solo pide a la API los que no están en caché (una petición por cada 100).
 * Si la API falla, usa las copias caducadas y pone $complete a false. Un 401 se relanza.
 */
function cached_profiles(array $ids, string $token, ?bool &$complete = null): array
{
    $complete = true;
    $profiles = [];
    $stale = [];
    $missing = [];
    foreach (array_unique(array_map('intval', $ids)) as $id) {
        $basic = profile_cache_read($id)['basic'] ?? [];
        if (profile_cache_fresh($basic)) {
            $profiles[$id] = $basic;
        } else {
            $missing[] = $id;
            if ($basic) {
                $stale[$id] = $basic;
            }
        }
    }
    if (!$missing) {
        return $profiles;
    }

    try {
        $fetched = ft_users_by_ids($missing, $token);
    } catch (FtApiException $ex) {
        if ($ex->getCode() === 401) {
            throw $ex;
        }
        $complete = false;
        return $profiles + $stale;
    }

    foreach ($fetched as $id => $profile) {
        $cache = profile_cache_read($id);
        $cache['basic'] = slim_basic($profile) + ['fetched_at' => time()];
        profile_cache_write($id, $cache);
        $profiles[$id] = $cache['basic'];
    }
    return $profiles;
}

/**
 * Datos de la ficha de un mentor (2 peticiones si no están en caché: perfil y coaliciones).
 * Si la API falla y hay copia caducada, se usa. Si no la hay, o es un 401, lanza FtApiException.
 */
function cached_mentor_detail(int $userId, string $token): array
{
    $cache = profile_cache_read($userId);
    if (profile_cache_fresh($cache['detail'] ?? [])) {
        return $cache['detail'];
    }

    try {
        $profile = ft_get('/v2/users/' . $userId, $token);
        $coalitions = ft_get('/v2/users/' . $userId . '/coalitions', $token);
    } catch (FtApiException $ex) {
        if ($ex->getCode() !== 401 && isset($cache['detail'])) {
            return $cache['detail'];
        }
        throw $ex;
    }

    // La ficha trae también nombre y foto: se aprovecha para renovar la parte de las listas
    $cache['basic'] = slim_basic($profile) + ['fetched_at' => time()];
    $cache['detail'] = slim_detail($profile, $coalitions) + ['fetched_at' => time()];
    profile_cache_write($userId, $cache);
    return $cache['detail'];
}
