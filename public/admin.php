<?php
// Administración: lista de mentores oficiales y manguitos. Solo para los logins de ADMIN_LOGINS (.env).
require __DIR__ . '/../src/bootstrap.php';

$user = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

    if ($action === 'add') {
        $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : '';
        if (!isset(ROSTER_ROLE_LABELS[$role])) {
            flash('Elige un rol válido.');
            redirect('admin.php');
        }
        $logins = preg_split('/[\s,;]+/', strtolower(is_string($_POST['logins'] ?? null) ? $_POST['logins'] : ''), -1, PREG_SPLIT_NO_EMPTY);
        $valid = array_unique(array_filter($logins, fn(string $l): bool => preg_match('/^[a-z0-9_-]{1,64}$/', $l) === 1));
        $invalid = array_diff($logins, $valid);

        $stmt = db()->prepare(
            'INSERT INTO roster (login, role, added_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE role = VALUES(role)'
        );
        foreach ($valid as $login) {
            $stmt->execute([$login, $role]);
        }
        $message = count($valid) . ' guardados como «' . ROSTER_ROLE_LABELS[$role] . '».';
        if ($invalid) {
            $message .= ' Logins no válidos: ' . implode(', ', array_slice($invalid, 0, 5)) . '.';
        }
        flash($message);
    } elseif ($action === 'remove') {
        $login = is_string($_POST['login'] ?? null) ? $_POST['login'] : '';
        db()->prepare('DELETE FROM roster WHERE login = ?')->execute([$login]);
        // Si era mentor oficial, deja de estar anunciado como disponible
        db()->prepare('UPDATE users SET available_until = NULL WHERE login = ?')->execute([$login]);
        flash('«' . $login . '» ya no está en la lista.');
    }
    redirect('admin.php');
}

$rows = db()->query(
    'SELECT r.login, r.role, r.added_at, u.id AS user_id, u.available_until,
            (u.available_until > NOW()) AS available_now
       FROM roster r
       LEFT JOIN users u ON u.login = r.login
      ORDER BY r.role, r.login'
)->fetchAll();

render_header('Administración', 'admin');
?>
<section class="card">
    <h1>Administración</h1>
    <p class="muted">Mentores oficiales y manguitos. Cada persona aparece en la web cuando inicia sesión y acepta
        el consentimiento; hasta entonces figura como «pendiente». Los cambios de rol se aplican la próxima vez que
        la persona inicie sesión.</p>

    <form method="post" class="stack">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <label class="field">
            <span>Logins <span class="muted small">(uno por línea, o separados por comas)</span></span>
            <textarea name="logins" rows="3" required placeholder="login1&#10;login2"></textarea>
        </label>
        <fieldset class="field">
            <legend>Rol</legend>
            <?php foreach (ROSTER_ROLE_LABELS as $value => $label): ?>
                <label class="radio">
                    <input type="radio" name="role" value="<?= e($value) ?>" <?= $value === 'mentor' ? 'checked' : '' ?>>
                    <?= e($label) ?>
                </label>
            <?php endforeach; ?>
            <p class="muted small">Si el login ya está en la lista, se cambia su rol.</p>
        </fieldset>
        <div class="actions">
            <button type="submit" class="btn">Añadir a la lista</button>
        </div>
    </form>
</section>

<section class="card">
    <h2>Lista (<?= count($rows) ?>)</h2>
    <?php if (!$rows): ?>
        <p class="muted">Todavía no hay nadie en la lista.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Login</th><th>Rol</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><a href="<?= e(intra_profile_url($row['login'])) ?>" target="_blank" rel="noopener"><?= e($row['login']) ?></a></td>
                    <td><?= e(ROSTER_ROLE_LABELS[$row['role']]) ?></td>
                    <td>
                        <?php if ($row['user_id'] === null): ?>
                            <span class="muted">Pendiente de aceptar</span>
                        <?php elseif ($row['role'] === 'mentor' && $row['available_now']): ?>
                            <span class="status online">Disponible hasta las <?= e(substr((string) $row['available_until'], 11, 5)) ?></span>
                        <?php else: ?>
                            Ha aceptado
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('¿Quitar a <?= e($row['login']) ?> de la lista?');">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="login" value="<?= e($row['login']) ?>">
                            <button type="submit" class="link">Quitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
