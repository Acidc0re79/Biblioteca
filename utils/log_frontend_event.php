<?php
// /utils/log_frontend_event.php (Versión Depurada)

// Cargamos la configuración una sola vez al principio.
require_once __DIR__ . '/../config/init.php';

// Si el modo de depuración no está activo, no hay nada que hacer.
if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
    http_response_code(204); // 204 No Content, la petición fue exitosa pero no hay respuesta.
    exit;
}

// Leemos el cuerpo de la petición AJAX.
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    http_response_code(400); // Bad Request
    exit;
}

// LOG_PATH se define en init.php.
$log_file = LOG_PATH . 'frontend_debug_log.json';
$log_dir = dirname($log_file);

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0775, true);
}

// Leemos los logs existentes.
$logs = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];
if (!is_array($logs)) {
    $logs = []; // Si el archivo está corrupto, lo reiniciamos.
}

// Creamos la nueva entrada de log.
$new_log_entry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'type' => $data['type'] ?? 'general_error',
    'details' => $data['details'] ?? 'No details provided',
    'user_id' => $_SESSION['user_id'] ?? 'invitado' // Leemos el ID de la sesión ya iniciada por init.php.
];

// Añadimos la nueva entrada al principio del array.
array_unshift($logs, $new_log_entry);

// Limitamos el archivo a las últimas 500 entradas para que no crezca indefinidamente.
$logs = array_slice($logs, 0, 500);

// Guardamos el archivo de logs actualizado.
file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Respondemos con éxito.
header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['status' => 'logged']);
exit;