<?php
// /utils/log_api_event.php

// Asegúrate de que ROOT_PATH esté definido (debe ser incluido después de init.php o definirlo aquí)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Asegurarse de que DEBUG_MODE esté definido (se define en init.php)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false); // Definir como falso por defecto si init.php no se ha cargado aún
}

function log_api_event($type, $details, $full_prompt_sent = null, $full_prompt_received = null) { // Añadido $full_prompt_sent y $full_prompt_received
    // Solo loguear eventos si DEBUG_MODE está activo
    if (!DEBUG_MODE) {
        return;
    }

    $log_file = ROOT_PATH . '/log/api_debug_log.json'; // Ruta al archivo de log

    // Asegurar que el directorio de log exista y sea escribible
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0775, true);
    }
    // Si el archivo no existe, crearlo con un array JSON vacío
    if (!file_exists($log_file)) {
        file_put_contents($log_file, json_encode([]));
        chmod($log_file, 0664); // Asegurar permisos de escritura
    }

    $logs = json_decode(file_get_contents($log_file), true);
    if ($logs === null) { // Fallback si el JSON está corrupto
        $logs = [];
    }

    $new_log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type, // 'gemini_prompt_attempt', 'huggingface_avatar_attempt'
        'details' => $details
    ];

    // Añadir el prompt completo si se proporcionó y el modo debug está activo
    if ($full_prompt_sent !== null) {
        $new_log_entry['full_prompt_sent'] = $full_prompt_sent;
    }
    if ($full_prompt_received !== null) {
        $new_log_entry['full_prompt_received'] = $full_prompt_received;
    }


    // Añadir el nuevo log al principio (los más actuales primero)
    array_unshift($logs, $new_log_entry);

    // Limitar el tamaño del log para que no crezca indefinidamente (ej. últimas 500 entradas)
    $logs = array_slice($logs, 0, 500);

    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}