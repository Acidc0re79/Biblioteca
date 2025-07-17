<?php
// Incluimos la configuración portable
require_once dirname(__DIR__, 2) . '/config/init.php';
$google_config = require_once ROOT_PATH . '/config/google_config.php';

// --- Parámetros para la URL de autorización de Google ---
$params = [
    'response_type' => 'code',
    'client_id'     => $google_config['client_id'],
    'redirect_uri'  => $google_config['redirect_uri'],
    'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
    'access_type'   => 'offline',
    'prompt'        => 'consent'
];

// Construimos la URL final
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// Redirigimos al usuario a la página de consentimiento de Google
header("Location: " . $auth_url);
exit;