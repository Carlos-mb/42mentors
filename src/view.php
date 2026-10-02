<?php
// Utilidades de presentación.
declare(strict_types=1);

/** Escapa texto para HTML. Usar SIEMPRE al imprimir datos. */
function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/** Mensaje de un solo uso para mostrar tras una redirección. */
function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function render_header(string $title, string $active = ''): void
{
    $user = current_user();
    $flash = flash();
    require ROOT_DIR . '/src/views/header.php';
}

function render_footer(): void
{
    require ROOT_DIR . '/src/views/footer.php';
}

/** Página de error sencilla. */
function render_error(string $message, int $status = 500): never
{
    http_response_code($status);
    render_header('Error');
    echo '<section class="card"><h1>Algo ha fallado</h1><p>' . e($message) . '</p>'
        . '<p><a class="btn" href="index.php">Volver al inicio</a></p></section>';
    render_footer();
    exit;
}

/** URL de la ficha de un usuario en la intra. */
function intra_profile_url(string $login): string
{
    return 'https://profile.intra.42.fr/users/' . rawurlencode($login);
}
