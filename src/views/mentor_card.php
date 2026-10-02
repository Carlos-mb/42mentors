<?php
/**
 * Tarjeta de un mentor (se incluye dentro de un <li>) que enlaza a su ficha.
 * Espera $mentor con: login, displayname, image_url, host, availability
 * y, opcionalmente: note (nota para un proyecto), bio, projects ([id => nombre]) e is_me.
 * @var array $mentor
 */
$cardProjects = $mentor['projects'] ?? [];
?>
<a class="mentor-card" href="mentor.php?login=<?= e(rawurlencode($mentor['login'])) ?>">
    <?php if ($mentor['image_url']): ?>
        <img src="<?= e($mentor['image_url']) ?>" alt="" class="avatar" loading="lazy">
    <?php else: ?>
        <span class="avatar placeholder"><?= e(strtoupper(substr($mentor['login'], 0, 1))) ?></span>
    <?php endif; ?>
    <span class="who">
        <strong><?= e($mentor['displayname'] ?: $mentor['login']) ?></strong>
        <span class="muted"><?= e($mentor['login']) ?><?= !empty($mentor['is_me']) ? ' · tú' : '' ?></span>
        <?php if (!empty($mentor['note'])): ?>
            <span class="note-text">«<?= e($mentor['note']) ?>»</span>
        <?php elseif (!empty($mentor['bio'])): ?>
            <span class="note-text"><?= e($mentor['bio']) ?></span>
        <?php endif; ?>
        <?php if ($cardProjects): ?>
            <span class="chips">
                <?php foreach (array_slice($cardProjects, 0, 4) as $projectName): ?>
                    <span class="chip"><?= e($projectName) ?></span>
                <?php endforeach; ?>
                <?php if (count($cardProjects) > 4): ?>
                    <span class="chip">+<?= count($cardProjects) - 4 ?></span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </span>
    <span class="side">
        <?php if ($mentor['host']): ?>
            <span class="status online">En el cluster · <?= e($mentor['host']) ?></span>
        <?php else: ?>
            <span class="status">No conectado</span>
        <?php endif; ?>
        <?= availability_badge($mentor['availability']) ?>
    </span>
</a>
