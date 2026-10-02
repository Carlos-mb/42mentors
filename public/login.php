<?php
// Paso 1 del OAuth: enviar al usuario a la intra para que autorice la app.
require __DIR__ . '/../src/bootstrap.php';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
redirect(ft_authorize_url($state));
