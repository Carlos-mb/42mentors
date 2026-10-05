<?php
// Mis valoraciones «Me ayudó»: las ve y las cambia solo quien las hizo (para los demás son anónimas).
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();

$stmt = db()->prepare(
    'SELECT v.mentor_id, v.project_id, v.value, u.login, p.name AS project_name,
            (mp.user_id IS NOT NULL AND u.campus_id <=> ?) AS can_change
       FROM votes v
       JOIN users u    ON u.id = v.mentor_id
       JOIN projects p ON p.id = v.project_id
       LEFT JOIN mentor_projects mp ON mp.user_id = v.mentor_id AND mp.project_id = v.project_id
      WHERE v.voter_id = ?
      ORDER BY u.login, p.name'
);
$stmt->execute([$user['campus_id'], $user['id']]);
$votes = $stmt->fetchAll();

render_header('Mis valoraciones', 'ratings');
?>
<section class="card">
    <h1>Mis valoraciones</h1>
    <p class="muted">Los mentores a los que has dicho «Me ayudó». Nadie más ve quién ha votado: tus valoraciones solo
        suman puntos a sus totales. Puedes cambiarlas cuando quieras.</p>

    <?php if (!$votes): ?>
        <p>Todavía no has valorado a nadie. Hazlo desde la ficha del mentor, junto a cada proyecto.</p>
        <p><a class="btn" href="mentors.php">Ver mentores</a></p>
    <?php else: ?>
        <ul class="ratings">
            <?php foreach ($votes as $vote): ?>
                <li id="vote-<?= (int) $vote['mentor_id'] ?>-<?= (int) $vote['project_id'] ?>">
                    <div>
                        <a href="mentor.php?login=<?= e(rawurlencode($vote['login'])) ?>"><strong><?= e($vote['login']) ?></strong></a>
                        · <?= e($vote['project_name']) ?>
                    </div>
                    <?php if ($vote['can_change']): ?>
                        <?= vote_form((int) $vote['mentor_id'], (int) $vote['project_id'], (int) $vote['value'], 'ratings') ?>
                    <?php else: ?>
                        <span class="muted small">
                            <?= e(VOTE_LABELS[(int) $vote['value']] ?? '') ?> · ya no ofrece ayuda con este proyecto
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
