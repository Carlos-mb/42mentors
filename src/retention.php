<?php
// Conservación de datos: se borra a quien lleva mucho tiempo sin entrar (condiciones de uso de la API de 42, art. 4.5).
declare(strict_types=1);

/** Meses sin iniciar sesión (last_login_at) tras los cuales se borran todos los datos de una persona. Ver privacy.php. */
const USER_RETENTION_MONTHS = 12;

/**
 * Borra a los usuarios inactivos y todo lo suyo (proyectos, valoraciones emitidas y recibidas, caché de su perfil).
 * No hay cron en el hosting: se llama al iniciar sesión y solo trabaja una vez al día (marca en cache/).
 * La lista de la administración (roster) no depende de la actividad y no se toca.
 */
function purge_inactive_users(): void
{
    $stamp = ROOT_DIR . '/cache/purge.stamp';
    if (is_file($stamp) && filemtime($stamp) > time() - 86400) {
        return;
    }
    touch($stamp);

    $months = USER_RETENTION_MONTHS;
    $ids = db()->query("SELECT id FROM users WHERE last_login_at < (NOW() - INTERVAL $months MONTH)")
        ->fetchAll(PDO::FETCH_COLUMN);
    if (!$ids) {
        return;
    }
    // mentor_projects y votes se borran en cascada
    $delete = db()->prepare('DELETE FROM users WHERE id = ?');
    foreach ($ids as $id) {
        $delete->execute([$id]);
        forget_profile((int) $id);
    }
    // La caché de ubicaciones puede contener su puesto
    foreach (glob(ROOT_DIR . '/cache/locations_*.json') ?: [] as $file) {
        unlink($file);
    }
}
