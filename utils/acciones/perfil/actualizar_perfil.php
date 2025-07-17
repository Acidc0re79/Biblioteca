<?php
// Este script asume que form-handler.php ya cargó init.php

// Seguridad: Verificar que el usuario está logueado y que la petición es POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_system_event("Intento de acceso no autorizado a actualizar_perfil.php");
    $_SESSION['error_message'] = "Acción no permitida.";
    header('Location: ' . BASE_URL);
    exit;
}

// Recoger y sanear los datos del formulario
$id_usuario = $_SESSION['user_id'];
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$tema_elegido = trim($_POST['tema'] ?? 'default');

// Validación básica
if (empty($nombre) || empty($apellido)) {
    $_SESSION['error_message'] = "El nombre y el apellido no pueden estar vacíos.";
    header("Location: " . BASE_URL . "index.php?p=perfil");
    exit;
}

try {
    // Verificamos que el tema elegido sea válido para evitar inyecciones maliciosas.
    $stmt_tema = $pdo->prepare("SELECT COUNT(*) FROM temas WHERE directorio = ? AND activo = 1");
    $stmt_tema->execute([$tema_elegido]);
    if ($stmt_tema->fetchColumn() == 0) {
        $tema_elegido = 'default'; // Si no es válido, volvemos al tema por defecto.
    }

    // Preparamos y ejecutamos la actualización.
    $stmt = $pdo->prepare(
        "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, descripcion = :descripcion, tema = :tema
         WHERE id_usuario = :id_usuario"
    );
    
    if ($stmt->execute([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'descripcion' => $descripcion,
        'tema' => $tema_elegido,
        'id_usuario' => $id_usuario
    ])) {
        // Actualizamos la sesión con los nuevos datos.
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['tema'] = $tema_elegido;
        
        log_system_event("Perfil de usuario actualizado con éxito.", ['id_usuario' => $id_usuario]);
        $_SESSION['success_message'] = "Perfil actualizado correctamente.";

    } else {
        log_system_event("Error al actualizar perfil de usuario en la BD.", ['id_usuario' => $id_usuario, 'errorInfo' => $stmt->errorInfo()]);
        $_SESSION['error_message'] = "No se pudo actualizar el perfil.";
    }

} catch (PDOException $e) {
    log_system_event("Excepción de BD al actualizar perfil.", ['id_usuario' => $id_usuario, 'error_message' => $e->getMessage()]);
    $_SESSION['error_message'] = "Error de base de datos.";
}

// Redirigimos de vuelta al perfil
header("Location: " . BASE_URL . "index.php?p=perfil");
exit;
?>