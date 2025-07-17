<?php
// Este script asume que ajax-handler.php ya cargó init.php

// Seguridad: Verificar que el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($password) || strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
    exit;
}
if ($password !== $password_confirm) {
    echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

try {
    // Creación de la contraseña segura
    $salt = bin2hex(random_bytes(16));
    $password_peppered = hash_hmac("sha256", $password, PEPPER);
    $hash_password = password_hash($password_peppered . $salt, PASSWORD_DEFAULT);

    // Actualizar el usuario en la base de datos
    $stmt = $pdo->prepare("UPDATE usuarios SET hash_password = ?, salt = ? WHERE id_usuario = ?");
    
    if ($stmt->execute([$hash_password, $salt, $user_id])) {
        // Registro de éxito
        log_system_event("Contraseña local creada para cuenta de Google.", ['id_usuario' => $user_id]);
        
        // Limpiamos el flag de la sesión
        unset($_SESSION['password_creation_required']);

        echo json_encode(['status' => 'success', 'message' => 'Contraseña creada con éxito. Ahora puedes iniciar sesión con tu correo y esta nueva contraseña.']);
    } else {
        log_system_event("Error al crear contraseña local.", ['id_usuario' => $user_id, 'error_info' => $stmt->errorInfo()]);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la contraseña.']);
    }

} catch (PDOException $e) {
    log_system_event("Excepción de BD al crear contraseña local.", ['id_usuario' => $user_id, 'error_message' => $e->getMessage()]);
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
}