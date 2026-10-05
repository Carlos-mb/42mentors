<?php
// Punto de entrada común: todas las páginas de public/ empiezan con
//   require __DIR__ . '/../src/bootstrap.php';
declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));

/** Carga ROOT_DIR/.env en $_ENV (formato CLAVE=valor, # para comentarios). */
function load_env(string $file): void
{
    if (!is_readable($file)) {
        http_response_code(500);
        exit('Falta el fichero .env: copia .env.example como .env y rellénalo.');
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        $_ENV[trim($key)] = $value;
    }
}

/** Lee una variable de configuración; sin $default, es obligatoria. */
function env(string $key, ?string $default = null): string
{
    $value = $_ENV[$key] ?? $default;
    if ($value === null) {
        throw new RuntimeException("Falta la variable $key en .env");
    }
    return $value;
}

load_env(ROOT_DIR . '/.env');

$debug = env('APP_DEBUG', '0') === '1';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

// Los datos de la API no pueden acabar en buscadores (condiciones de uso de la API de 42)
header('X-Robots-Tag: noindex, nofollow');

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name('ft_mentors');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $https,
    'httponly' => true,
    'samesite' => 'Lax', // Lax (no Strict): la vuelta desde la intra en el login es una navegación cross-site
]);
session_start();

require ROOT_DIR . '/src/db.php';
require ROOT_DIR . '/src/ft_api.php';
require ROOT_DIR . '/src/auth.php';
require ROOT_DIR . '/src/locations.php';
require ROOT_DIR . '/src/profiles.php';
require ROOT_DIR . '/src/mentors.php';
require ROOT_DIR . '/src/votes.php';
require ROOT_DIR . '/src/view.php';
