<?php
/**
 * Helper de logging dedicado para el Motor de IA.
 * Escribe en un archivo de log separado para facilitar la depuración.
 */

function log_ia_event($message, array $context = []) {
    // Si el modo debug no está activo, no hacemos nada.
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        return;
    }

    $log_file = LOG_PATH . 'ia_debug_log.json';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0775, true);
    }

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message'   => $message,
        'context'   => $context
    ];

    $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    file_put_contents($log_file, $log_line, FILE_APPEND);
}