<?php
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();
render_header(__('Inicio'));
?>
<?php if (!$user): ?>
    <section class="hero">
        <h1><?= e(__('¿Atascado en un proyecto?')) ?><br><?= e(__('Alguien de tu campus ya lo ha hecho.')) ?></h1>
        <p><?= e(__('Encuentra compañeros que han validado el proyecto que estás haciendo y ofrécete a ayudar con los que ya terminaste.')) ?></p>
        <a class="btn btn-big" href="login.php"><?= e(__('Entrar con 42')) ?></a>
    </section>
<?php else: ?>
    <section class="dash">
        <p class="dash-kicker">42 Mentors</p>
        <h1><?= e(__('Hola, {name}', ['name' => $user['displayname'] ?: $user['login']])) ?></h1>
        <p class="muted"><?= e(__('¿Qué quieres hacer hoy?')) ?></p>
        <div class="choices">
            <a class="choice choice-a" href="projects.php">
                <span class="choice-tag"><?= e(__('Proyectos')) ?></span>
                <strong><?= e(__('Busco ayuda con un proyecto')) ?></strong>
                <span><?= e(__('Elige el proyecto y mira quién de tu campus puede ayudarte.')) ?></span>
            </a>
            <a class="choice choice-b" href="mentors.php">
                <span class="choice-tag"><?= e(__('Directorio')) ?></span>
                <strong><?= e(__('Ver mentores')) ?></strong>
                <span><?= e(__('Busca a alguien por nombre o login y mira quién está en el cluster.')) ?></span>
            </a>
            <a class="choice choice-c" href="profile.php">
                <span class="choice-tag"><?= e(__('Perfil')) ?></span>
                <strong><?= e(__('Mi perfil de mentor')) ?></strong>
                <span><?= e(__('Elige con qué proyectos ayudas y cuenta cómo encontrarte.')) ?></span>
            </a>
            <?php if (can_see_available($user)): ?>
                <a class="choice choice-d" href="available.php">
                    <span class="choice-tag"><?= e(__('Manguito')) ?></span>
                    <strong><?= e(__('Mentores disponibles ahora')) ?></strong>
                    <span><?= e(__('Mira qué mentores oficiales de tu campus pueden ayudarte ahora mismo.')) ?></span>
                </a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
