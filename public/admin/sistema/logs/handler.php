<?php
// /admin/handler_logs.php (Versión con Botón para Copiar)

require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/config/init.php';

$log_file = ROOT_PATH . '/log/handler_debug_log.json';
$logs = [];

if (file_exists($log_file)) {
    $logs = json_decode(file_get_contents($log_file), true);
    if ($logs === null) {
        $logs = [['timestamp' => date('Y-m-d H:i:s'), 'handler_type' => 'ERROR', 'details' => ['message' => 'El archivo de log handler_debug_log.json está corrupto.']]];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Logs de Handlers - Admin</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
  <style>
    .log-entry { background-color: #f4f4f4; border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; position: relative; }
    .log-entry h4 { margin: 0 0 10px 0; }
    .log-entry pre { background-color: #fff; padding: 10px; border: 1px solid #ccc; max-height: 300px; overflow-y: auto; }
    /* ✅ NUEVOS ESTILOS PARA EL BOTÓN DE COPIAR */
    .copy-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        font-size: 12px;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        opacity: 0.7;
        transition: opacity 0.3s;
    }
    .copy-btn:hover {
        opacity: 1;
    }
    .copy-btn.copied {
        background-color: #28a745;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="admin-layout">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <section class="admin-content">
      <h1>Logs de Handlers (Controladores)</h1>
      <p>Registros de las peticiones recibidas por los controladores principales.</p>
      
      <div class="log-list">
        <?php if (empty($logs)): ?>
          <div class="log-entry"><p>No hay registros de handlers todavía.</p></div>
        <?php else: ?>
          <?php foreach ($logs as $i => $log): ?>
            <div class="log-entry">
              <?php
                $log_type = $log['handler_type'] ?? 'desconocido';
                $timestamp = $log['timestamp'] ?? 'N/A';
                $user_id = $log['user_id'] ?? 'invitado';
                // Generamos un ID único para la sección del log que queremos copiar
                $log_content_id = "log-content-" . $i;
                $log_content_json = json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
              ?>
              <button class="copy-btn" data-target-id="<?= $log_content_id ?>">Copiar</button>
              
              <h4><?= htmlspecialchars($timestamp) ?> - Tipo: <?= htmlspecialchars($log_type) ?></h4>
              <p><strong>Usuario ID:</strong> <?= htmlspecialchars($user_id) ?></p>

              <div><strong>Log Completo:</strong>
                <pre id="<?= $log_content_id ?>"><?= htmlspecialchars($log_content_json) ?></pre>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('copy-btn')) {
            const button = event.target;
            const targetId = button.dataset.targetId;
            const contentElement = document.getElementById(targetId);

            if (contentElement) {
                // Usamos la API del portapapeles, que es la forma moderna y segura
                navigator.clipboard.writeText(contentElement.textContent).then(function() {
                    // Éxito al copiar
                    button.textContent = '¡Copiado!';
                    button.classList.add('copied');
                    setTimeout(() => {
                        button.textContent = 'Copiar';
                        button.classList.remove('copied');
                    }, 2000); // El botón vuelve a su estado original después de 2 segundos
                }, function(err) {
                    // Fallo al copiar
                    console.error('Error al copiar el texto: ', err);
                    alert('No se pudo copiar el log.');
                });
            }
        }
    });
  </script>

</body>
</html>