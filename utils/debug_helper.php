<?php
// /utils/debug_helper.php (Versión 3 - Escribe en JSON)

/**
 * Registra un evento del sistema en un archivo JSON si el modo de depuración está activado.
 *
 * @param string $message El mensaje principal del evento.
 * @param array $context Un array asociativo con datos adicionales.
 */
function log_system_event($message, $context = [])
{
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        return; // No hacer nada si el modo de depuración está apagado.
    }

    // LOG_PATH debe ser definida en init.php (ej. ROOT_PATH . '/logs/')
    if (!defined('LOG_PATH')) {
        // Fallback por si acaso, aunque no debería ocurrir.
        define('LOG_PATH', dirname(__DIR__, 2) . '/logs/');
    }

    $log_file = LOG_PATH . 'system_debug.json';
    $log_dir = dirname($log_file);

    // Crea el directorio de logs si no existe.
    if (!is_dir($log_dir)) {
        // 0775 da permisos completos al propietario y grupo, y de lectura/ejecución a otros.
        mkdir($log_dir, 0775, true);
    }

    $new_log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level'     => 'DEBUG',
        'message'   => $message,
        'context'   => $context
    ];

    // Convertimos la nueva entrada a una cadena JSON y añadimos un salto de línea.
    $log_line = json_encode($new_log_entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    // Añadimos la nueva línea al final del archivo. FILE_APPEND evita sobreescribir.
    file_put_contents($log_file, $log_line, FILE_APPEND);
}