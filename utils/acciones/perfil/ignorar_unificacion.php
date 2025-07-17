<?php
// /utils/acciones/perfil/ignorar_unificacion.php
require_once ROOT_PATH . '/config/init.php';

// Solo funciona si el usuario está logueado.
if (!isset($_SESSION['usuario_id'])) {
    die('Acceso no permitido.');
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // Actualizamos la base de datos para este usuario.
    $stmt = $pdo->prepare("UPDATE usuarios SET ignorar_unificacion_pwd = 1 WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);

    // También eliminamos la bandera de la sesión actual.
    if (isset($_SESSION['password_creation_required'])) {
        unset($_SESSION['password_creation_required']);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
    exit;
}
?>