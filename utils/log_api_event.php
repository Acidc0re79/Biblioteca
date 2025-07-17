<?php
// /utils/log_api_event.php (Versión Depurada y Simplificada)

/**
 * Registra un evento de API en un archivo JSON si el modo de depuración está activado.
 *
 * @param string $type El tipo de evento (ej. 'gemini_success', 'huggingface_error').
 * @param array $details Un array con los detalles del evento a registrar.
 */
function log_api_event($type, $details)
{
    // Este script asume que init.php ya fue cargado y DEBUG_MODE ya está definida.
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        return; // No hacer nada si el modo de depuración está apagado.
    }

    // LOG_PATH debe ser definida en init.php para mayor consistencia.
    $log_file = LOG_PATH . 'api_debug_log.json';
    $log_dir = dirname($log_file);

    // Crea el directorio de logs si no existe.
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0775, true);
    }

    $new_log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'details' => $details
    ];

    // Convertimos la nueva entrada a una cadena JSON.
    $log_line = json_encode($new_log_entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Añadimos la nueva línea al final del archivo sin sobreescribir.
    file_put_contents($log_file, $log_line . PHP_EOL, FILE_APPEND);
}