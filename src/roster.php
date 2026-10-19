<?php
// Mentores oficiales de la Operación Manguito y manguitos: se declaran ellos mismos, con un enlace privado (join_page()).
// La marca va en users.manguito_role y solo existe tras aceptar el consentimiento. El administrador solo puede quitarla (admin.php).
declare(strict_types=1);

const ROSTER_ROLE_LABELS = [
    'mentor'   => 'Mentor oficial',
    'manguito' => 'Manguito',
];

/** Página con la que se declara cada rol y variable del .env con el código que hay que añadir al enlace (?c=...). */
const JOIN_PAGES = ['mentor' => 'mentor-manguito.php', 'manguito' => 'soy-manguito.php'];
const JOIN_CODE_ENV = ['mentor' => 'MENTOR_INVITE_CODE', 'manguito' => 'MANGUITO_INVITE_CODE'];

/** Máximo de horas que puede anunciar un mentor oficial que está disponible (para que no se quede un aviso olvidado). */
const AVAILABLE_MAX_HOURS = 8;

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

/** Rol de un usuario al iniciar sesión: la marca que él mismo se puso o, con MANGUITOS_ALL, manguito. */
function user_role(int $userId): ?string
{
    $stmt = db()->prepare('SELECT manguito_role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $role = $stmt->fetchColumn();
    return is_string($role) ? $role : (manguitos_all() ? 'manguito' : null);
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

/** Condición SQL: usuarios de $alias que se han marcado como mentores oficiales. */
function sql_is_official(string $alias = 'u'): string
{
    return "$alias.manguito_role = 'mentor'";
}

/** Código que hay que añadir al enlace de una de las páginas de declaración ('' = página desactivada). */
function join_code(string $role): string
{
    return env(JOIN_CODE_ENV[$role], '');
}

/**
 * Página privada en la que alguien se declara mentor oficial o manguito. No tiene enlaces desde la web:
 * sin el código correcto (?c=…, del .env) responde 404. Con él, guarda una marca en la sesión y redirige a la
 * URL sin código (para que no quede en el historial ni en el Referer); después hay que iniciar sesión con 42 y
 * haber aceptado el consentimiento, porque la marca se guarda en users.
 */
function join_page(string $role): never
{
    $page = JOIN_PAGES[$role];
    $code = join_code($role);
    $flag = 'invite_' . $role;

    if (isset($_GET['c'])) {
        if ($code !== '' && is_string($_GET['c']) && hash_equals($code, $_GET['c'])) {
            $_SESSION[$flag] = true;
            redirect($page);
        }
        render_error('Página no encontrada.', 404);
    }
    if ($code === '' || empty($_SESSION[$flag])) {
        render_error('Página no encontrada.', 404);
    }

    // Sin sesión (o con el token caducado) se inicia sesión y se vuelve aquí; no se usa relogin() para no perder la marca
    $user = current_user();
    if ($user === null || ($_SESSION['token_expires_at'] ?? 0) <= time() + 30) {
        unset($_SESSION['user'], $_SESSION['projects'], $_SESSION['access_token'], $_SESSION['token_expires_at']);
        $_SESSION['login_return'] = $page;
        redirect('login.php');
    }

    $consented = !empty($user['consented']);
    $stmt = db()->prepare('SELECT manguito_role, manguito_blocked FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch() ?: ['manguito_role' => null, 'manguito_blocked' => 0];
    $blocked = (bool) $row['manguito_blocked'];
    $current = $row['manguito_role'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (!$consented) {
            redirect('consent.php?next=' . rawurlencode($page));
        }
        if (($_POST['action'] ?? '') === 'leave') {
            db()->prepare('UPDATE users SET manguito_role = NULL, available_until = NULL WHERE id = ?')->execute([$user['id']]);
            $_SESSION['user']['role'] = manguitos_all() ? 'manguito' : null;
            flash('Ya no estás marcado como ' . mb_strtolower(ROSTER_ROLE_LABELS[$role]) . '.');
            redirect('index.php');
        }
        if ($blocked) {
            render_error('No puedes marcarte ahora mismo. Si crees que es un error, escribe al contacto de la página de privacidad.', 403);
        }
        // Un solo rol a la vez: marcarse como uno quita el otro (y el aviso de disponibilidad si lo hubiera)
        db()->prepare(
            'UPDATE users SET manguito_role = ?, available_until = IF(? = \'mentor\', available_until, NULL)
              WHERE id = ? AND manguito_blocked = 0'
        )->execute([$role, $role, $user['id']]);
        $_SESSION['user']['role'] = $role;
        unset($_SESSION[$flag]);
        flash('Hecho: ahora figuras como ' . mb_strtolower(ROSTER_ROLE_LABELS[$role]) . ' de la Operación Manguito.');
        redirect('available.php');
    }

    render_header('Operación Manguito', 'available');
    ?>
    <section class="card">
        <?php if ($role === 'mentor'): ?>
            <h1>Mentor de la Operación Manguito</h1>
            <p>Si eres uno de los mentores de la Operación Manguito, márcalo aquí. Podrás anunciar desde «Manguito» hasta
                qué hora estás disponible, y los estudiantes de tu campus que hayan iniciado sesión verán tu nombre, tu
                foto y tu puesto en el cluster en «Disponibles ahora».</p>
        <?php else: ?>
            <h1>Manguito</h1>
            <p>Si eres manguito, márcalo aquí para ver «Disponibles ahora»: los mentores de tu campus que pueden
                ayudarte en este momento. Que eres manguito solo lo ven tú y el responsable de la web.</p>
        <?php endif; ?>

        <?php if ($blocked): ?>
            <p class="warn">Ahora mismo no puedes marcarte. Si crees que es un error, escribe al contacto de la
                <a href="privacy.php">página de privacidad</a>.</p>
        <?php elseif (!$consented): ?>
            <p>Antes necesitamos que aceptes cómo tratamos tus datos, porque guardamos esa marca.</p>
            <p class="actions"><a class="btn" href="consent.php?next=<?= e(rawurlencode($page)) ?>">Continuar</a></p>
        <?php elseif ($current === $role): ?>
            <p>Ya figuras como <strong><?= e(mb_strtolower(ROSTER_ROLE_LABELS[$role])) ?></strong>.</p>
            <form method="post" class="actions">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" name="action" value="leave" class="link">Dejar de figurar así</button>
                <a href="available.php">Ir a «Manguito»</a>
            </form>
        <?php else: ?>
            <form method="post" class="actions">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" name="action" value="join" class="btn">Soy <?= e(mb_strtolower(ROSTER_ROLE_LABELS[$role])) ?></button>
            </form>
            <?php if ($current !== null): ?>
                <p class="muted small">Ahora figuras como <?= e(mb_strtolower(ROSTER_ROLE_LABELS[(string) $current])) ?>: al marcarte aquí, dejas de serlo.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}
