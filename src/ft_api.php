<?php
// Cliente mínimo de la API de 42 (ver API-Docs/md/guides/).
declare(strict_types=1);

const FT_API = 'https://api.intra.42.fr';

class FtApiException extends RuntimeException
{
}

/**
 * Petición HTTP a la API. Devuelve el JSON decodificado.
 * Reintenta ante 429 (límite: 2 peticiones/segundo por app).
 * Lanza FtApiException con el código HTTP como code (0 = fallo de conexión).
 */
function ft_http(string $method, string $url, ?array $form = null, ?string $token = null): array
{
    $headers = ['Accept: application/json'];
    if ($token !== null) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    for ($attempt = 0; ; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => $headers,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form ?? []));
        }
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new FtApiException('No se pudo conectar con la API de 42: ' . $error, 0);
        }
        if ($status === 429 && $attempt < 2) {
            sleep(1);
            continue;
        }
        break;
    }

    if ($status >= 400) {
        throw new FtApiException("La API de 42 respondió con el código $status", $status);
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new FtApiException('Respuesta no válida de la API de 42', $status);
    }
    return $data;
}

/** URL a la que se envía al usuario para que autorice la app (paso 1 del OAuth). */
function ft_authorize_url(string $state): string
{
    return FT_API . '/oauth/authorize?' . http_build_query([
        'client_id'     => env('FT_CLIENT_ID'),
        'redirect_uri'  => env('FT_REDIRECT_URI'),
        'response_type' => 'code',
        'scope'         => 'public',
        'state'         => $state,
    ]);
}

/** Cambia el code de la vuelta del OAuth por un token (paso 2, siempre en servidor). */
function ft_exchange_code(string $code): array
{
    return ft_http('POST', FT_API . '/oauth/token', [
        'grant_type'    => 'authorization_code',
        'client_id'     => env('FT_CLIENT_ID'),
        'client_secret' => env('FT_CLIENT_SECRET'),
        'code'          => $code,
        'redirect_uri'  => env('FT_REDIRECT_URI'),
    ]);
}

/** GET a un endpoint de la API, p. ej. ft_get('/v2/me', $token). */
function ft_get(string $path, string $token, array $query = []): array
{
    $url = FT_API . $path . ($query ? '?' . http_build_query($query) : '');
    return ft_http('GET', $url, null, $token);
}

/** Perfiles (nombre, foto…) de varios usuarios a la vez: [id => perfil]. Una petición por cada 100. */
function ft_users_by_ids(array $ids, string $token): array
{
    $profiles = [];
    $chunks = array_chunk(array_values(array_unique(array_map('intval', $ids))), 100);
    foreach ($chunks as $i => $chunk) {
        if ($i > 0) {
            usleep(500000); // respetar 2 peticiones/segundo
        }
        $query = ['filter' => ['id' => implode(',', $chunk)], 'page' => ['size' => 100]];
        foreach (ft_get('/v2/users', $token, $query) as $profile) {
            $profiles[(int) $profile['id']] = $profile;
        }
    }
    return $profiles;
}

/** GET paginado: junta todas las páginas (100 elementos por página). */
function ft_get_all(string $path, string $token, array $query = [], int $maxPages = 10): array
{
    $all = [];
    for ($page = 1; $page <= $maxPages; $page++) {
        $query['page'] = ['size' => 100, 'number' => $page];
        $items = ft_get($path, $token, $query);
        $all = array_merge($all, $items);
        if (count($items) < 100) {
            break;
        }
        usleep(500000); // respetar 2 peticiones/segundo
    }
    return $all;
}
