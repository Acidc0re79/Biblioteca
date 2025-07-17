<?php
require_once '../../config/init.php';
require_once 'includes/auth.php'; // Script de seguridad que verifica si el usuario es admin/mod.

// Función para leer archivos de log que son JSON línea por línea.
function get_system_logs($logName) {
    $logPath = ROOT_PATH . '/logs/' . $logName;
    $logs = [];
    if (file_exists($logPath)) {
        $log_lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($log_lines as $line) {
            $decoded_line = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $logs[] = $decoded_line;
            }
        }
        return array_reverse($logs); // Mostramos los logs más recientes primero.
    }
    return $logs;
}

$system_logs = get_system_logs('system_debug.json');
$page_title = "Logs del Sistema";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Panel de Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
    
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/nav.php'; ?>
        <div class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="admin-content">
                <h2><?php echo $page_title; ?></h2>
                <p>Eventos generales del sistema (logins, registros, errores, etc.) cuando el modo depuración está activo.</p>

                <div class="logs-container-scrollable">
                    <?php if (empty($system_logs)): ?>
                        <div class="log-card">
                            <div class="log-card-body">
                                <p>No hay entradas en el log del sistema.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($system_logs as $index => $log): ?>
                            <div class="log-card">
                                <div class="log-card-header">
                                    <span class="log-chip log-<?php echo strtolower(htmlspecialchars($log['level'] ?? 'default')); ?>">
                                        <?php echo strtoupper(htmlspecialchars($log['level'] ?? 'LOG')); ?>
                                    </span>
                                    <span class="log-timestamp"><?php echo htmlspecialchars($log['timestamp'] ?? ''); ?></span>
                                    <button class="copy-btn" data-target="log-content-<?php echo $index; ?>">Copiar</button>
                                </div>
                                <div class="log-card-body">
                                    <p><strong><?php echo htmlspecialchars($log['message'] ?? 'Mensaje no disponible'); ?></strong></p>
                                    <?php if (!empty($log['context'])): ?>
                                    <pre id="log-content-<?php echo $index; ?>"><?php echo htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/logs_viewer.js"></script>
</body>
</html>