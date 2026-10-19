<?php
// Administración: quién se ha marcado como mentor oficial o manguito y los enlaces privados para hacerlo.
// Solo para los logins de ADMIN_LOGINS (.env). El administrador no da de alta a nadie: solo puede quitar la marca.
require __DIR__ . '/../src/bootstrap.php';

$user = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    $login = is_string($_POST['login'] ?? null) ? $_POST['login'] : '';

    if ($action === 'block') {
        // Quita la marca y no deja que se la vuelva a poner; también deja de estar anunciado como disponible
        db()->prepare('UPDATE users SET manguito_role = NULL, manguito_blocked = 1, available_until = NULL WHERE login = ?')
            ->execute([$login]);
        flash(__('«{login}» ya no figura y no puede volver a marcarse. Lo verá la próxima vez que inicie sesión.', ['login' => $login]));
    } elseif ($action === 'unblock') {
        db()->prepare('UPDATE users SET manguito_blocked = 0 WHERE login = ?')->execute([$login]);
        flash(__('«{login}» puede volver a marcarse.', ['login' => $login]));
    }
    redirect('admin.php');
}

$rows = db()->query(
    'SELECT login, manguito_role, manguito_blocked, available_until, (available_until > NOW()) AS available_now
       FROM users
      WHERE manguito_role IS NOT NULL OR manguito_blocked = 1
      ORDER BY manguito_blocked, manguito_role, login'
)->fetchAll();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');

render_header(__('Administración'), 'admin');
?>
<section class="card">
    <h1><?= e(__('Administración')) ?></h1>
    <p class="muted"><?= e(__('Mentores oficiales y manguitos se marcan ellos mismos, con un enlace privado, después de aceptar el consentimiento. Aquí puedes mandar esos enlaces y quitar la marca a quien haga un uso indebido.')) ?></p>

    <?php if (manguitos_all()): ?>
        <p class="warn"><?= e(__('MANGUITOS_ALL está activo: todos los usuarios ven «Disponibles ahora», se hayan marcado o no.')) ?></p>
    <?php endif; ?>

    <h2><?= e(__('Enlaces privados')) ?></h2>
    <p class="muted small"><?= _h('No tienen enlaces desde la web: solo funcionan con el código. Si un enlace se filtra, cambia su código en el {env} (las marcas ya puestas se quitan abajo).', [], ['env' => '<code>.env</code>']) ?></p>
    <?php foreach (JOIN_PAGES as $role => $page): ?>
        <?php $code = join_code($role); ?>
        <label class="field">
            <span><?= e(__(ROSTER_ROLE_LABELS[$role])) ?></span>
            <?php if ($code === ''): ?>
                <span class="warn"><?= e(__('Desactivada: falta {var} en el .env.', ['var' => JOIN_CODE_ENV[$role]])) ?></span>
            <?php else: ?>
                <input type="text" readonly value="<?= e($base . '/' . $page . '?c=' . $code) ?>" onclick="this.select()">
            <?php endif; ?>
        </label>
    <?php endforeach; ?>
</section>

<section class="card">
    <h2><?= e(__('Marcados y bloqueados')) ?> (<?= count($rows) ?>)</h2>
    <?php if (!$rows): ?>
        <p class="muted"><?= e(__('Todavía no se ha marcado nadie.')) ?></p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Login</th><th><?= e(__('Rol')) ?></th><th><?= e(__('Estado')) ?></th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <?php
                $confirm = $row['manguito_blocked']
                    ? __('¿Dejar que {login} vuelva a marcarse?', ['login' => $row['login']])
                    : __('¿Quitar la marca y no dejar que {login} vuelva a marcarse?', ['login' => $row['login']]);
                ?>
                <tr>
                    <td><a href="<?= e(intra_profile_url($row['login'])) ?>" target="_blank" rel="noopener"><?= e($row['login']) ?></a></td>
                    <td><?= $row['manguito_role'] === null ? '—' : e(__(ROSTER_ROLE_LABELS[$row['manguito_role']])) ?></td>
                    <td>
                        <?php if ($row['manguito_blocked']): ?>
                            <span class="muted"><?= e(__('Bloqueado')) ?></span>
                        <?php elseif ($row['manguito_role'] === 'mentor' && $row['available_now']): ?>
                            <span class="status online"><?= e(__('Disponible hasta las')) ?> <?= e(local_time((string) $row['available_until'])) ?></span>
                        <?php else: ?>
                            <?= e(__('Marcado')) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm(<?= e(json_encode($confirm, JSON_UNESCAPED_UNICODE)) ?>);">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="<?= $row['manguito_blocked'] ? 'unblock' : 'block' ?>">
                            <input type="hidden" name="login" value="<?= e($row['login']) ?>">
                            <button type="submit" class="link"><?= e($row['manguito_blocked'] ? __('Desbloquear') : __('Quitar y bloquear')) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
