<?php
require_once __DIR__ . '/includes/auth.php'; // Seguridad primero
require_once dirname(__DIR__, 2) . '/config/init.php';

$log_file = ROOT_PATH . '/log/frontend_debug_log.json';
$logs = [];

if (file_exists($log_file)) {
    $json_data = file_get_contents($log_file);
    $logs = json_decode($json_data, true);
    if ($logs === null) {
        $logs = [['timestamp' => date('Y-m-d H:i:s'), 'type' => 'ERROR', 'details' => ['message' => 'El archivo de log frontend_debug_log.json está corrupto.']]];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Logs de Frontend - Admin</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
  <link rel="stylesheet" href="/admin/assets/css/logs.css"> </head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="admin-layout">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <section class="admin-content">
      <h1>Logs de Errores de Frontend (JavaScript)</h1>
      <p>Registros de errores capturados desde el navegador del usuario cuando el modo depuración está activo.</p>

      <div class="log-list">
        <?php if (empty($logs)): ?>
          <div class="log-entry info"><p>No hay registros de errores de frontend todavía.</p></div>
        <?php else: ?>
          <?php foreach ($logs as $log): ?>
            <div class="log-entry error">
              <h4><?= htmlspecialchars($log['timestamp']) ?> - Tipo: <?= htmlspecialchars($log['type']) ?></h4>
              <p><strong>Usuario ID:</strong> <?= htmlspecialchars($log['user_id']) ?></p>
              <div><strong>Detalles:</strong> 
                <pre><?= htmlspecialchars(json_encode($log['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>