<?php
// Este script asume que form-handler.php ya cargó init.php

// Seguridad: Verificar que el usuario tenga rol de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rango'] !== 'administrador') {
    log_system_event("Intento de eliminación de usuario no autorizado.", ['usuario_intentando' => $_SESSION['user_id'] ?? 'No logueado']);
    $_SESSION['error_message'] = "No tienes permisos para realizar esta acción.";
    header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
    exit;
}

if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
    $_SESSION['error_message'] = "No se proporcionó un ID de usuario para eliminar.";
    header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
    exit;
}

$id_usuario_a_eliminar = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

// Seguridad: Un administrador no puede eliminarse a sí mismo.
if ($id_usuario_a_eliminar === (int)$_SESSION['user_id']) {
    $_SESSION['error_message'] = "No puedes eliminar tu propia cuenta desde el panel.";
    header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    
    if ($stmt->execute([$id_usuario_a_eliminar])) {
        // Registro de éxito
        log_system_event("Usuario eliminado exitosamente desde el panel.", [
            'admin_id' => $_SESSION['user_id'],
            'usuario_eliminado_id' => $id_usuario_a_eliminar
        ]);
        $_SESSION['success_message'] = "Usuario eliminado correctamente.";
    } else {
        // Registro de fallo
        log_system_event("Error al intentar eliminar usuario desde el panel.", [
            'admin_id' => $_SESSION['user_id'],
            'usuario_a_eliminar_id' => $id_usuario_a_eliminar,
            'error_info' => $stmt->errorInfo()
        ]);
        $_SESSION['error_message'] = "Error al eliminar el usuario.";
    }

} catch (PDOException $e) {
    log_system_event("Excepción de BD al eliminar usuario.", ['error_message' => $e->getMessage()]);
    $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
}

header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
exit;
?>