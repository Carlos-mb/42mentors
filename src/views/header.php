<?php
/** @var ?array $user  @var ?string $flash  @var string $title  @var string $active */
$langSwitch = function (): void { ?>
    <div class="lang" role="group" aria-label="<?= e(__('Idioma')) ?>">
        <?php foreach (['es' => 'ES', 'en' => 'EN'] as $code => $label): ?>
            <a href="<?= e(lang_url($code)) ?>" hreflang="<?= $code ?>" lang="<?= $code ?>"
               title="<?= $code === 'es' ? 'Español' : 'English' ?>"<?= current_lang() === $code ? ' aria-current="true"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
    </div>
<?php };
?><!doctype html>
<html lang="<?= current_lang() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · 42 Mentors</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= e(APP_VERSION) ?>">
    <?php if ($user): ?>
        <link rel="stylesheet" href="assets/session.css?v=<?= e(APP_VERSION) ?>">
    <?php endif; ?>
</head>
<body<?= $user ? ' class="session"' : '' ?>>
<header class="topbar">
    <a class="brand" href="index.php">42<span>Mentors</span></a>
    <?php if ($user): ?>
        <nav>
            <a href="projects.php" class="<?= $active === 'projects' ? 'active' : '' ?>"><?= e(__('Proyectos')) ?></a>
            <a href="mentors.php" class="<?= $active === 'mentors' ? 'active' : '' ?>"><?= e(__('Mentores')) ?></a>
            <a href="profile.php" class="<?= $active === 'profile' ? 'active' : '' ?>"><?= e(__('Mi perfil')) ?></a>
            <?php if (votes_enabled()): ?>
                <a href="ratings.php" class="<?= $active === 'ratings' ? 'active' : '' ?>"><?= e(__('Mis valoraciones')) ?></a>
            <?php endif; ?>
            <?php if (can_see_available($user)): ?>
                <a href="available.php" class="<?= $active === 'available' ? 'active' : '' ?>"><?= e(__('Manguito')) ?></a>
            <?php endif; ?>
            <?php if (is_admin($user['login'])): ?>
                <a href="admin.php" class="<?= $active === 'admin' ? 'active' : '' ?>"><?= e(__('Administración')) ?></a>
            <?php endif; ?>
        </nav>
        <div class="me">
            <?php if ($user['image_url']): ?>
                <img src="<?= e($user['image_url']) ?>" alt="" referrerpolicy="no-referrer" class="avatar-sm" data-initial="<?= e(strtoupper(substr($user['login'], 0, 1))) ?>">
            <?php endif; ?>
            <span><?= e($user['login']) ?></span>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="link"><?= e(__('Salir')) ?></button>
            </form>
            <?php $langSwitch(); ?>
        </div>
    <?php else: ?>
        <?php $langSwitch(); ?>
    <?php endif; ?>
</header>
<main>
<?php if ($flash): ?>
    <div class="flash"><?= e($flash) ?></div>
<?php endif; ?>
