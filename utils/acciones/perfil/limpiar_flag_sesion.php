<?php
// /utils/acciones/perfil/limpiar_flag_sesion.php
require_once ROOT_PATH . '/config/init.php';

// Simplemente elimina la bandera de la sesión actual.
if (isset($_SESSION['password_creation_required'])) {
    unset($_SESSION['password_creation_required']);
}

// Respondemos con éxito para que el JavaScript sepa que funcionó.
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;
?>