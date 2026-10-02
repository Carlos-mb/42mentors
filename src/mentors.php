<?php
// Mentores: campos del perfil, datos de la API (nunca se guardan) y utilidades de presentación.
declare(strict_types=1);

const AVAILABILITY_LABELS = [
    'available' => 'Disponible',
    'busy'      => 'Ocupado',
    'paused'    => 'En pausa',
];

const CONTACT_LABELS = [
    'cluster' => 'En persona, en el cluster',
    'slack'   => 'Por Slack',
    'both'    => 'En persona o por Slack',
];

const LANGUAGE_LABELS = [
    'es' => 'Español',
    'en' => 'English',
    'fr' => 'Français',
    'pt' => 'Português',
    'it' => 'Italiano',
    'de' => 'Deutsch',
    'ca' => 'Català',
];

/** Idiomas guardados ("es,en") → códigos válidos, en el orden de LANGUAGE_LABELS. */
function parse_languages(?string $value): array
{
    return array_values(array_intersect(array_keys(LANGUAGE_LABELS), explode(',', (string) $value)));
}

/** Normaliza un texto libre escrito por el usuario: espacios simples y longitud máxima. */
function clean_text(string $text, int $max): string
{
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

/** Foto de un perfil de la API (null si no tiene). */
function profile_image(array $profile, string $size = 'medium'): ?string
{
    return $profile['image']['versions'][$size] ?? $profile['image']['link'] ?? null;
}

/**
 * Completa filas de mentores de la BD con nombre y foto (caché de perfiles; a la API solo los caducados)
 * y su puesto en el cluster (caché por campus). Nada de esto se guarda en la BD.
 * Devuelve false si la API falló (faltan fotos o ubicaciones). Si el token caducó, vuelve al login.
 */
function enrich_mentors(array &$mentors, ?int $campusId): bool
{
    $profiles = [];
    $locations = [];
    $ok = true;
    if ($mentors) {
        try {
            $profiles = cached_profiles(array_column($mentors, 'id'), user_token(), $ok);
            if ($campusId !== null) {
                $locations = active_locations($campusId, user_token());
            }
        } catch (FtApiException $ex) {
            if ($ex->getCode() === 401) {
                relogin();
            }
            $ok = false;
        }
    }
    foreach ($mentors as &$mentor) {
        $profile = $profiles[(int) $mentor['id']] ?? [];
        $mentor['displayname'] = $profile['displayname'] ?? null;
        $mentor['image_url'] = profile_image($profile);
        $mentor['host'] = $locations[(int) $mentor['id']] ?? null;
    }
    unset($mentor);
    return $ok;
}

/** Orden: conectados primero, luego disponibles antes que ocupados, luego por login. */
function sort_mentors(array &$mentors): void
{
    usort($mentors, fn(array $a, array $b): int =>
        [$a['host'] === null, $a['availability'] === 'busy', $a['login']]
        <=> [$b['host'] === null, $b['availability'] === 'busy', $b['login']]);
}

/** Etiqueta de disponibilidad (HTML). «Disponible» solo se muestra si se pide: es lo normal. */
function availability_badge(string $availability, bool $showAvailable = false): string
{
    if ($availability === 'available' && !$showAvailable) {
        return '';
    }
    $label = AVAILABILITY_LABELS[$availability] ?? $availability;
    return '<span class="badge badge-' . e($availability) . '">' . e($label) . '</span>';
}

/** Cursus principal de un perfil de la API: 42cursus si lo tiene; si no, el de nivel más alto. */
function main_cursus(array $profile): ?array
{
    $best = null;
    foreach ($profile['cursus_users'] ?? [] as $cursusUser) {
        if (($cursusUser['cursus']['slug'] ?? '') === '42cursus') {
            return $cursusUser;
        }
        if ($best === null || (float) ($cursusUser['level'] ?? 0) > (float) ($best['level'] ?? 0)) {
            $best = $cursusUser;
        }
    }
    return $best;
}

/** 7.4219 → "7,42" */
function format_level(mixed $level): string
{
    return number_format((float) $level, 2, ',', '.');
}

/** Fecha de la API o de la BD → "mar 2025" (null si no hay fecha válida). */
function month_year(?string $date): ?string
{
    if ($date === null || $date === '') {
        return null;
    }
    try {
        $parsed = new DateTimeImmutable($date);
    } catch (Exception $ex) {
        return null;
    }
    $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    return $months[(int) $parsed->format('n') - 1] . ' ' . $parsed->format('Y');
}

/** Color de la API solo si es un hex válido (se usa dentro de un atributo style). */
function safe_color(mixed $color): ?string
{
    return is_string($color) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : null;
}
