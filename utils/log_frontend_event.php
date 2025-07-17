<?php
require_once dirname(__DIR__) . '/config/init.php';

// Solo se ejecuta si el modo de depuración está activado en la base de datos
if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    exit;
}

$log_file = ROOT_PATH . '/log/frontend_debug_log.json';
$log_dir = dirname($log_file);

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0775, true);
}

$logs = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];
if (!is_array($logs)) {
    $logs = []; // Si el archivo está corrupto, empezamos de cero
}

$new_log_entry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'type' => $data['type'] ?? 'general_error',
    'details' => $data['details'] ?? 'No details provided',
    'user_id' => $_SESSION['usuario_id'] ?? 'invitado'
];

array_unshift($logs, $new_log_entry);
$logs = array_slice($logs, 0, 500); // Limitar a las últimas 500 entradas

file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Respondemos con un 200 OK para que la petición fetch se complete
http_response_code(200);
echo json_encode(['status' => 'logged']);
?>