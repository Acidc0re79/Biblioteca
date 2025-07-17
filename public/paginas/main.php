<?php
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Función para leer archivos de log que son JSON línea por línea
function get_logs_from_file($logName) {
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
        return array_reverse($logs); // Mostrar los más recientes primero
    }
    return $logs;
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/logs_viewer.css?v=<?php echo time(); ?>">

<div class="container mt-4">
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Bienvenido a la Biblioteca Digital SYS</h1>
            <p class="col-md-8 fs-4">Tu portal al conocimiento y la aventura.</p>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (defined('DEBUG_MODE') && DEBUG_MODE === true): ?>
        <div class="card mt-4 logs-section">
            <h5 class="card-header">Consola de Depuración Activa</h5>
            <div class="card-body">
                <?php $system_logs = get_logs_from_file('system_debug.json'); ?>
                <?php if (empty($system_logs)): ?>
                    <p>El log del sistema está vacío.</p>
                <?php else: ?>
                    <?php foreach ($system_logs as $index => $log): ?>
                        <div class="log-card">
                            <div class="log-card-header">
                                <span class="log-chip log-<?= strtolower($log['level'] ?? 'default') ?>">
                                    <?= strtoupper($log['level'] ?? 'LOG') ?>
                                </span>
                                <span class="log-timestamp"><?= $log['timestamp'] ?? '' ?></span>
                                <button class="copy-btn" data-target="log-content-<?= $index ?>">Copiar</button>
                            </div>
                            <div class="log-card-body">
                                <p><strong><?= htmlspecialchars($log['message'] ?? 'Mensaje no disponible') ?></strong></p>
                                <?php if (!empty($log['context'])): ?>
                                <pre id="log-content-<?= $index ?>"><?= htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/logs_viewer.js?v=<?php echo time(); ?>"></script>