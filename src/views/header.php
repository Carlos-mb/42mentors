<?php
/** @var ?array $user  @var ?string $flash  @var string $title  @var string $active */
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · 42 Mentors</title>
    <link rel="stylesheet" href="assets/style.css">
    <?php if ($user): ?>
        <link rel="stylesheet" href="assets/session.css">
    <?php endif; ?>
</head>
<body<?= $user ? ' class="session"' : '' ?>>
<header class="topbar">
    <a class="brand" href="index.php">42<span>Mentors</span></a>
    <?php if ($user): ?>
        <nav>
            <a href="projects.php" class="<?= $active === 'projects' ? 'active' : '' ?>">Proyectos</a>
            <a href="mentors.php" class="<?= $active === 'mentors' ? 'active' : '' ?>">Mentores</a>
            <a href="profile.php" class="<?= $active === 'profile' ? 'active' : '' ?>">Mi perfil</a>
        </nav>
        <div class="me">
            <?php if ($user['image_url']): ?>
                <img src="<?= e($user['image_url']) ?>" alt="" class="avatar-sm">
            <?php endif; ?>
            <span><?= e($user['login']) ?></span>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="link">Salir</button>
            </form>
        </div>
    <?php endif; ?>
</header>
<main>
<?php if ($flash): ?>
    <div class="flash"><?= e($flash) ?></div>
<?php endif; ?>
