<?php
// /public/admin/api_logs.php (Versión 2.1 - A prueba de errores)

require_once '../../config/init.php';
require_once 'includes/auth.php'; // Asegura que solo admin/mod pueden ver esto

$log_file = ROOT_PATH . '/public/log/api_debug_log.json';
$logs = [];

if (file_exists($log_file)) {
    // Leemos el archivo línea por línea
    $log_lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($log_lines as $line) {
        $decoded_line = json_decode($line, true);
        // Nos aseguramos que la decodificación fue exitosa antes de añadirlo
        if (json_last_error() === JSON_ERROR_NONE) {
            $logs[] = $decoded_line;
        }
    }
    // Mostramos los logs más recientes primero
    $logs = array_reverse($logs);
}

$page_title = "Logs de API";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Panel de Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/logs.css"> </head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/nav.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="admin-content">
                <h2><?php echo $page_title; ?></h2>
                <p>Aquí se registran las interacciones con las APIs externas cuando el modo de depuración está activo.</p>

                <div class="logs-container">
                    <?php if (empty($logs)): ?>
                        <div class="log-card">
                            <div class="log-card-body">
                                <p>No hay entradas en el log de API.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($logs as $index => $log): ?>
                            <div class="log-card">
                                <div class="log-card-header">
                                    <span class="log-chip log-<?php echo htmlspecialchars($log['type'] ?? 'default'); ?>">
                                        <?php echo htmlspecialchars(strtoupper($log['type'] ?? '')); ?>
                                    </span>
                                    <span class="log-timestamp"><?php echo htmlspecialchars($log['timestamp'] ?? ''); ?></span>
                                    <button class="copy-btn" data-target="log-content-<?php echo $index; ?>">Copiar</button>
                                </div>
                                <div class="log-card-body">
                                    <pre id="log-content-<?php echo $index; ?>"><?php
                                        if (isset($log['details'])) {
                                            echo htmlspecialchars(json_encode($log['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                        } else {
                                            echo htmlspecialchars(json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                        }
                                    ?></pre>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.logs-container');
    if (container) {
        container.addEventListener('click', function(event) {
            if (event.target.classList.contains('copy-btn')) {
                const button = event.target;
                const targetId = button.dataset.target;
                const logContentElement = document.getElementById(targetId);

                if (logContentElement) {
                    navigator.clipboard.writeText(logContentElement.textContent).then(() => {
                        const originalText = button.textContent;
                        button.textContent = '¡Copiado!';
                        button.classList.add('copied');
                        
                        setTimeout(() => {
                            button.textContent = originalText;
                            button.classList.remove('copied');
                        }, 2000);
                    }).catch(err => {
                        console.error('Error al copiar el log: ', err);
                        alert('No se pudo copiar el texto.');
                    });
                }
            }
        });
    }
});
</script>

</body>
</html>