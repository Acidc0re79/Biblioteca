<?php
// /utils/acciones/auth/procesar_login.php (Versión Autosuficiente y Final)

// ✅ INICIALIZACIÓN PROPIA: El script ahora llama a init.php por sí mismo.
// Usamos la constante ROOT_PATH que ya fue definida por el handler.
require_once ROOT_PATH . '/config/init.php';

// Ahora que tenemos $pdo, el resto del script funciona sin cambios.
$pepper_path = ROOT_PATH . '/config/pepper.key';
if (!file_exists($pepper_path)) {
    die('Error de configuración: No se encuentra el archivo pepper.key.');
}
$pepper = trim(file_get_contents($pepper_path));

if (empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: /index.php?pagina=login_form&error=campos");
    exit;
}

$email = strtolower(trim($_POST['email']));
$password = $_POST['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: /index.php?pagina=login_form&error=usuario");
        exit;
    }
    
    if ($usuario['estado_cuenta'] !== 'activo') {
        header("Location: /index.php?pagina=login_form&error=estado");
        exit;
    }

    $peppered = hash_hmac("sha256", $password, $pepper);
    $pass_verificar = $peppered . $usuario['salt'];

    if (password_verify($pass_verificar, $usuario['hash_password'])) {
        session_start(); // Aseguramos que la sesión esté iniciada
        session_unset();
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        // ...etcétera...
        session_write_close();
        
        header("Location: /index.php");
        exit;
    } else {
        header("Location: /index.php?pagina=login_form&error=clave");
        exit;
    }

} catch (PDOException $e) {
    error_log("Error de BD en procesar_login.php: " . $e->getMessage());
    header("Location: /index.php?pagina=login_form&error=db");
    exit;
}
?>