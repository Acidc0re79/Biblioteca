<?php
// Se han eliminado los mensajes de sesión de la parte superior para unificar
// la gestión de notificaciones en el futuro.

// La función para leer logs y toda la lógica de la consola de depuración
// han sido completamente eliminadas de este archivo público.
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/logs_viewer.css?v=<?php echo time(); ?>">

<div class="container mt-4">
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Bienvenido a la Biblioteca Digital SYS</h1>
            <p class="col-md-8 fs-4">Tu portal al conocimiento y la aventura.</p>
        </div>
    </div>

    <?php
    // Mantenemos la lógica para mostrar mensajes de éxito o error al usuario
    // que son redirigidos aquí.
    if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/logs_viewer.js?v=<?php echo time(); ?>"></script>