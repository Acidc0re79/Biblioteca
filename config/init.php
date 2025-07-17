<?php
// /config/init.php (Versión a prueba de fallos)

// Iniciar la sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ CORRECCIÓN CLAVE:
// Definimos la constante ROOT_PATH solo si no ha sido definida antes.
// Esto evita el error fatal.
if (!defined('ROOT_PATH')) {
    // dirname(__DIR__) toma la ruta de la carpeta actual (/config) y sube un nivel.
    define('ROOT_PATH', dirname(__DIR__));
}

// El resto de la inicialización (base de datos, claves, etc.) no cambia.
require_once ROOT_PATH . '/config/db.php';
$api_keys = require_once ROOT_PATH . '/config/api_keys.php';

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
?>