<?php
// /config/init.php (Versión 2, Corregida sin Composer)

// Iniciar la sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se define la constante ROOT_PATH solo si no ha sido definida antes.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// --- CARGA DE CONFIGURACIONES Y HELPERS ESENCIALES ---

// Incluimos la conexión a la base de datos para que $pdo esté disponible globalmente.
require_once ROOT_PATH . '/config/db.php';

// Incluimos el helper de depuración para que esté disponible en todo el sitio.
require_once ROOT_PATH . '/utils/debug_helper.php';

// Incluimos el helper de funciones generales.
require_once ROOT_PATH . '/utils/helpers.php';

// Incluimos las claves de API para que estén disponibles.
$api_keys = require ROOT_PATH . '/config/api_keys.php';

// Definimos las claves y el pepper como constantes para fácil acceso.
if (!defined('GEMINI_API_KEYS')) {
    define('GEMINI_API_KEYS', $api_keys['gemini']);
}
if (!defined('HUGGINGFACE_API_KEY')) {
    define('HUGGINGFACE_API_KEY', $api_keys['huggingface'][0]);
}
if (!defined('PEPPER')) {
    define('PEPPER', trim(file_get_contents(ROOT_PATH . '/config/pepper.key')));
}

// Cargamos la configuración de Google
$google_config = require ROOT_PATH . '/config/google_config.php';
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', $google_config['client_id']);
    define('GOOGLE_CLIENT_SECRET', $google_config['client_secret']);
    define('GOOGLE_REDIRECT_URI', $google_config['redirect_uri']);
}


// --- CARGA DE CONFIGURACIÓN DINÁMICA DESDE LA BD ---
try {
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (!defined('CONFIG_SITIO')) {
        define('CONFIG_SITIO', $settings);
    }
    if (!defined('DEBUG_MODE')) {
        define('DEBUG_MODE', (isset(CONFIG_SITIO['modo_depuracion']) && CONFIG_SITIO['modo_depuracion'] == '1'));
    }

} catch (PDOException $e) {
    if (!defined('CONFIG_SITIO')) define('CONFIG_SITIO', []);
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
    error_log("Error al cargar la configuración del sitio: " . $e->getMessage());
}

// --- OTRAS CONSTANTES Y CONFIGURACIONES GLOBALES ---

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $host . '/');
}

if (!defined('AVATARS_PATH')) {
    define('AVATARS_PATH', ROOT_PATH . '/public/uploads/avatars/');
}

if (!defined('AVATARS_URL')) {
    define('AVATARS_URL', BASE_URL . 'uploads/avatars');
}

if (!defined('LOG_PATH')) {
    define('LOG_PATH', ROOT_PATH . '/logs/');
}

// La llamada al autoloader de Composer ha sido eliminada.