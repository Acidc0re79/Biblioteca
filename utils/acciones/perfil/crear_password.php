<?php
// /utils/acciones/perfil/crear_password.php (Versión Final Corregida)

// Usamos el ROOT_PATH que nos pasa el form-handler
require_once ROOT_PATH . '/config/init.php';

// ✅ CORRECCIÓN: Verificamos la sesión principal del usuario, no una temporal.
// Esto confirma que el usuario está legítimamente logueado y necesita crear una contraseña.
if (!isset($_SESSION['password_creation_required']) || !isset($_SESSION['usuario_id'])) {
    die('Error: No tienes permiso para realizar esta acción.');
}

$password = $_POST['password'] ?? '';
$confirmar_password = $_POST['confirmar_password'] ?? '';
$id_usuario = $_SESSION['usuario_id']; // Usamos el ID de la sesión real

if (empty($password) || $password !== $confirmar_password) {
    header('Location: /index.php?pagina=perfil&error=password_mismatch');
    exit;
}
    
$pepper = trim(file_get_contents(ROOT_PATH . '/config/pepper.key'));
$salt = bin2hex(random_bytes(16));
$password_peppered = hash_hmac("sha256", $password, $pepper);
$hash = password_hash($password_peppered . $salt, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET hash_password = ?, salt = ? WHERE id_usuario = ?");
    $stmt->execute([$hash, $salt, $id_usuario]);

    // --- UNIFICACIÓN COMPLETA ---
    // Limpiamos la bandera de la sesión, ya que la tarea se ha completado.
    unset($_SESSION['password_creation_required']);
    
    // Redirigimos al perfil. El usuario ya tiene una sesión completa y activa.
    header("Location: /index.php?pagina=perfil&unificacion=ok");
    exit;

} catch (PDOException $e) {
    // En caso de un error de base de datos, lo registramos y mostramos un error.
    error_log("Error al actualizar la contraseña: " . $e->getMessage());
    die("Error crítico: No se pudo actualizar la contraseña. Por favor, contacta al administrador.");
}
?>