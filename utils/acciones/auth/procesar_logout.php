<?php
// Este script asume que el form-handler ya cargó init.php

// Registramos el evento de logout antes de destruir la sesión para no perder el user_id.
if (isset($_SESSION['user_id'])) {
    log_system_event("Cierre de sesión exitoso.", ['id_usuario' => $_SESSION['user_id']]);
}

// Limpiamos todas las variables de sesión.
session_unset();

// Destruimos la sesión completamente.
session_destroy();

// Redirigimos al usuario a la página principal.
header('Location: ' . BASE_URL);
exit;