<?php
// Sesión del usuario y protección CSRF.
declare(strict_types=1);

/** Usuario logueado (o null). Se rellena en callback.php. */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Exige sesión válida; si no la hay o el token caducó, vuelve a pasar por el login de 42. */
function require_login(): array
{
    $user = current_user();
    if ($user === null || ($_SESSION['token_expires_at'] ?? 0) <= time() + 30) {
        relogin();
    }
    return $user;
}

/** ¿Ha aceptado el usuario que guardemos sus datos? (tabla users = solo usuarios que aceptaron) */
function user_has_consented(int $userId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return (bool) $stmt->fetchColumn();
}

/** Exige consentimiento para las páginas que guardan datos; si no lo hay, lo pide. */
function require_consent(): array
{
    $user = require_login();
    if (empty($user['consented'])) {
        redirect('consent.php');
    }
    return $user;
}

/** Token de acceso del usuario para llamar a la API. */
function user_token(): string
{
    return $_SESSION['access_token'];
}

/** Borra la sesión y manda de nuevo al login (si el usuario ya autorizó la app, es transparente). */
function relogin(): never
{
    $_SESSION = [];
    redirect('login.php');
}

/** Proyectos del usuario guardados al iniciar sesión, indexados por id de proyecto. */
function user_projects(): array
{
    return $_SESSION['projects'] ?? [];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Petición no válida. Recarga la página e inténtalo de nuevo.');
    }
}
