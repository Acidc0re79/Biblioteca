<?php
// /config/init.php (Versión 3.1, con parser de .env mejorado)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// --- FUNCIÓN PARA CARGAR EL .ENV ---
function load_environment_variables($path) {
    if (!is_readable($path)) {
        throw new RuntimeException(sprintf('%s file is not readable', $path));
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, '"');
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

try {
    load_environment_variables(ROOT_PATH . '/.env');
} catch (RuntimeException $e) {
    die("Error crítico: No se puede leer el archivo de configuración .env. Asegúrate de que exista en la raíz del proyecto.");
}

// --- CONSTANTES DE SEGURIDAD Y APIS ---
if (!defined('PEPPER')) define('PEPPER', getenv('APP_PEPPER'));
if (!defined('GOOGLE_CLIENT_ID')) define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
if (!defined('GOOGLE_CLIENT_SECRET')) define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
if (!defined('GOOGLE_REDIRECT_URI')) define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI'));

// ✅ MEJORA: Usamos array_map('trim', ...) para eliminar espacios en blanco de cada clave.
if (!defined('GEMINI_API_KEYS')) define('GEMINI_API_KEYS', array_map('trim', explode(',', getenv('GEMINI_API_KEYS'))));

if (!defined('HUGGINGFACE_API_KEY')) define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY'));

// --- CONEXIÓN A BASE DE DATOS Y HELPERS ---
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/utils/debug_helper.php';
require_once ROOT_PATH . '/utils/helpers.php';


// --- CONFIGURACIÓN DINÁMICA DESDE LA BD (sin cambios) ---
try {
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (!defined('CONFIG_SITIO')) define('CONFIG_SITIO', $settings);
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', (isset(CONFIG_SITIO['modo_depuracion']) && CONFIG_SITIO['modo_depuracion'] == '1'));

} catch (PDOException $e) {
    if (!defined('CONFIG_SITIO')) define('CONFIG_SITIO', []);
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
    error_log("Error al cargar la configuración del sitio: " . $e->getMessage());
}

// --- OTRAS CONSTANTES (sin cambios) ---
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $host . '/');
}
if (!defined('AVATARS_PATH')) define('AVATARS_PATH', ROOT_PATH . '/public/uploads/avatars/');
if (!defined('AVATARS_URL')) define('AVATARS_URL', BASE_URL . 'uploads/avatars');
if (!defined('LOG_PATH')) define('LOG_PATH', ROOT_PATH . '/logs/');