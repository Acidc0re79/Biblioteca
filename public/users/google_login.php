<?php
// 1. Carga toda la configuración y el entorno de la aplicación.
// Las constantes de Google ya están definidas aquí.
require_once dirname(__DIR__, 2) . '/config/init.php';

// 2. Usamos las constantes globales directamente. No es necesario volver a incluir el archivo.
$params = [
    'response_type' => 'code',
    'client_id'     => GOOGLE_CLIENT_ID,     // Usamos la constante
    'redirect_uri'  => GOOGLE_REDIRECT_URI,  // Usamos la constante
    'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
    'access_type'   => 'offline',
    'prompt'        => 'consent'
];

// 3. Construimos la URL final y redirigimos.
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

header("Location: " . $auth_url);
exit;
?>