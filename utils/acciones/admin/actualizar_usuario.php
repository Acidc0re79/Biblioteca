<?php
// Este script asume que form-handler.php ya cargó init.php

// Seguridad: Verificar que el usuario tenga rol de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rango'] !== 'administrador') {
    log_system_event("Intento de edición de usuario no autorizado.", ['usuario_intentando' => $_SESSION['user_id'] ?? 'No logueado']);
    $_SESSION['error_message'] = "No tienes permisos para realizar esta acción.";
    header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
    exit;
}

// Recoger y validar los datos
$id_usuario_a_editar = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
$nuevo_rango = $_POST['rango'] ?? '';
$nuevo_estado = $_POST['estado_cuenta'] ?? '';

// Validar que los valores recibidos son los permitidos
$rangos_permitidos = ['lector', 'moderador', 'administrador'];
$estados_permitidos = ['pendiente', 'activo', 'suspendido', 'baneado'];

if (!$id_usuario_a_editar || !in_array($nuevo_rango, $rangos_permitidos) || !in_array($nuevo_estado, $estados_permitidos)) {
    $_SESSION['error_message'] = "Datos inválidos para la actualización.";
    header('Location: ' . BASE_URL . 'admin/index.php?p=usuarios');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET rango = ?, estado_cuenta = ? WHERE id_usuario = ?");
    
    if ($stmt->execute([$nuevo_rango, $nuevo_estado, $id_usuario_a_editar])) {
        // Registro de éxito
        log_system_event("Usuario actualizado exitosamente desde el panel.", [
            'admin_id' => $_SESSION['user_id'],
            'usuario_editado_id' => $id_usuario_a_editar,
            'nuevos_datos' => ['rango' => $nuevo_rango, 'estado' => $nuevo_estado]
        ]);
        $_SESSION['success_message'] = "Usuario actualizado correctamente.";
    } else {
        // Registro de fallo
        log_system_event("Error al intentar actualizar usuario desde el panel.", [
            'admin_id' => $_SESSION['user_id'],
            'usuario_a_editar_id' => $id_usuario_a_editar,
            'error_info' => $stmt->errorInfo()
        ]);
        $_SESSION['error_message'] = "Error al actualizar el usuario.";
    }

} catch (PDOException $e) {
    log_system_event("Excepción de BD al editar usuario.", ['error_message' => $e->getMessage()]);
    $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
}

header('Location: ' . BASE_URL . 'admin/index.php?p=editar_usuario&id=' . $id_usuario_a_editar);
exit;
?>