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
    <section class="dash">
        <p class="dash-kicker">42 Mentors</p>
        <h1>Hola, <?= e($user['displayname'] ?: $user['login']) ?></h1>
        <p class="muted">¿Qué quieres hacer hoy?</p>
        <div class="choices">
            <a class="choice choice-a" href="projects.php">
                <span class="choice-tag">Proyectos</span>
                <strong>Busco ayuda con un proyecto</strong>
                <span>Elige el proyecto y mira quién de tu campus puede ayudarte.</span>
            </a>
            <a class="choice choice-b" href="mentors.php">
                <span class="choice-tag">Directorio</span>
                <strong>Ver mentores</strong>
                <span>Busca a alguien por nombre o login y mira quién está en el cluster.</span>
            </a>
            <a class="choice choice-c" href="profile.php">
                <span class="choice-tag">Perfil</span>
                <strong>Mi perfil de mentor</strong>
                <span>Elige con qué proyectos ayudas y cuenta cómo encontrarte.</span>
            </a>
        </div>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
