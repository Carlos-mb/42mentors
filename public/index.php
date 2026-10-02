<?php
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();
render_header('Inicio');
?>
<?php if (!$user): ?>
    <section class="hero">
        <h1>¿Atascado en un proyecto?<br>Alguien de tu campus ya lo ha hecho.</h1>
        <p>Encuentra compañeros que han validado el proyecto que estás haciendo y ofrécete a ayudar con los que ya terminaste.</p>
        <a class="btn btn-big" href="login.php">Entrar con 42</a>
    </section>
<?php else: ?>
    <section class="hero">
        <h1>Hola, <?= e($user['displayname'] ?: $user['login']) ?></h1>
        <p>¿Qué quieres hacer hoy?</p>
        <div class="choices">
            <a class="choice" href="projects.php">
                <strong>Busco ayuda con un proyecto</strong>
                <span>Elige el proyecto y mira quién de tu campus puede ayudarte.</span>
            </a>
            <a class="choice" href="mentors.php">
                <strong>Ver mentores</strong>
                <span>Busca a alguien por nombre o login y mira quién está en el cluster.</span>
            </a>
            <a class="choice" href="profile.php">
                <strong>Mi perfil de mentor</strong>
                <span>Elige con qué proyectos ayudas y cuenta cómo encontrarte.</span>
            </a>
        </div>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
