<?php
// Quién está conectado en el cluster ahora mismo.
declare(strict_types=1);

/**
 * Ubicaciones activas de los MENTORES de un campus: [user_id => host].
 * Se pide a la API una vez para todo el campus y se cachea LOCATIONS_CACHE_TTL segundos
 * en cache/, en lugar de hacer una petición por mentor (límite de la API: 1200 peticiones/hora).
 * Solo se cachean mentores con consentimiento y algún proyecto (quien solo vota también está en users).
 */
function active_locations(int $campusId, string $token): array
{
    $file = ROOT_DIR . '/cache/locations_' . $campusId . '.json';
    $ttl = (int) env('LOCATIONS_CACHE_TTL', '120');

    if (is_file($file) && filemtime($file) > time() - $ttl) {
        $cached = json_decode((string) file_get_contents($file), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $stmt = db()->prepare(
        'SELECT id FROM users u
          WHERE campus_id = ? AND EXISTS (SELECT 1 FROM mentor_projects mp WHERE mp.user_id = u.id)'
    );
    $stmt->execute([$campusId]);
    $consented = array_flip(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));

    $items = ft_get_all("/v2/campus/$campusId/locations", $token, ['filter' => ['active' => 'true']]);
    $map = [];
    foreach ($items as $location) {
        if (isset($location['user']['id'], $location['host'], $consented[(int) $location['user']['id']])) {
            $map[(int) $location['user']['id']] = (string) $location['host'];
        }
    }

    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    file_put_contents($file, json_encode($map), LOCK_EX);
    return $map;
}
