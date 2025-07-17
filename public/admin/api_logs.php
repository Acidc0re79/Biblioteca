<?php
// /admin/api_logs.php
// Página de administración para leer los logs de la API.

require_once __DIR__ . '/includes/auth.php'; // Asegurar que solo admins/moderadores puedan acceder
require_once dirname(__DIR__, 2) . '/utils/log_api_event.php'; // Incluir la función de log para acceder a la ruta del archivo

$log_file = ROOT_PATH . '/log/api_debug_log.json';
$logs = [];

if (file_exists($log_file)) {
    $logs = json_decode(file_get_contents($log_file), true);
    if ($logs === null) { // Si el JSON está corrupto
        $logs = [['timestamp' => date('Y-m-d H:i:s'), 'type' => 'ERROR', 'details' => 'Archivo de log JSON corrupto.']];
    }
} else {
    $logs = [['timestamp' => date('Y-m-d H:i:s'), 'type' => 'INFO', 'details' => 'El archivo de log aún no existe o está vacío.']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Logs de API - Biblioteca SYS Admin</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
  <style>
    /* Estilos específicos para esta página de logs */
    .log-entry {
        background-color: #333;
        color: #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        font-family: monospace;
        white-space: pre-wrap; /* Mantiene saltos de línea y respeta espacios */
        word-break: break-word; /* Rompe palabras largas */
    }
    .log-entry.error { background-color: #c0392b; }
    .log-entry.success { background-color: #27ae60; }
    .log-entry.warning { background-color: #f39c12; }

    .log-entry h4 {
        margin-top: 0;
        margin-bottom: 5px;
        color: #fff;
        font-size: 1.1em;
    }
    .log-entry p {
        margin: 0;
        font-size: 0.9em;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="admin-layout">
    <?php include __DIR__ . '/includes/nav.php'; ?>

    <section class="admin-content">
      <h1>Logs de Actividad de API</h1>
      <p>Aquí puedes ver el historial de llamadas a las APIs de IA, incluyendo intentos de clave y respuestas.</p>
      
      <div class="log-list">
        <?php foreach ($logs as $log): ?>
          <?php 
            $log_class = '';
            if (strpos($log['details']['status'] ?? '', 'Error') !== false || strpos($log['details']['response'] ?? '', '"success":false') !== false || $log['type'] === 'ERROR') {
                $log_class = 'error';
            } elseif ($log['details']['status'] ?? '' === 'OK' || strpos($log['details']['response'] ?? '', '"success":true') !== false) {
                $log_class = 'success';
            } else if (strpos($log['details']['status'] ?? '', 'Advertencia') !== false) {
                $log_class = 'warning';
            }
          ?>
          <div class="log-entry <?= $log_class ?>">
            <h4><?= htmlspecialchars($log['timestamp']) ?> - Tipo: <?= htmlspecialchars($log['type']) ?></h4>
            <?php foreach ($log['details'] as $key => $value): ?>
                <p><strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?>:</strong> 
                <?php 
                    // Formatear JSON si es una cadena JSON
                    if (is_string($value) && (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))) {
                        $json_decoded = json_decode($value);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            echo '<pre>' . htmlspecialchars(json_encode($json_decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>';
                        } else {
                            echo htmlspecialchars($value);
                        }
                    } else if (is_array($value)) {
                        echo htmlspecialchars(json_encode($value)); // Para arrays pequeños
                    }
                    else {
                        echo htmlspecialchars($value);
                    }
                ?>
                </p>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
</body>
</html>