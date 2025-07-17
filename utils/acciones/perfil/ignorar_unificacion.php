<?php
// Este script asume que ajax-handler.php ya cargó init.php

// Seguridad: Verificar que el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['user_id'];

try {
    // Actualizamos la base de datos para este usuario.
    $stmt = $pdo->prepare("UPDATE usuarios SET ignorar_unificacion_pwd = 1 WHERE id_usuario = ?");
    
    if ($stmt->execute([$id_usuario])) {
        // Registro de éxito
        log_system_event("Usuario eligió ignorar unificación de contraseña.", ['id_usuario' => $id_usuario]);
        
        // También eliminamos la bandera de la sesión actual.
        unset($_SESSION['password_creation_required']);
        
        echo json_encode(['status' => 'success', 'message' => 'Preferencia guardada.']);

    } else {
        log_system_event("Error al actualizar la preferencia de ignorar unificación.", ['id_usuario' => $id_usuario, 'errorInfo' => $stmt->errorInfo()]);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la preferencia.']);
    }

} catch (PDOException $e) {
    log_system_event("Excepción de BD al ignorar unificación.", ['id_usuario' => $id_usuario, 'error_message' => $e->getMessage()]);
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
}
?>