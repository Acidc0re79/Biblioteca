<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
// Este script asume que un manejador (ej. form-handler.php) ya ha:
// 1. Iniciado la sesión y cargado la configuración con init.php.
// 2. Creado la conexión $pdo a la base de datos.
// 3. Cargado el helper de depuración.

// --- Verificación de Seguridad Adicional ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

// Recogemos y saneamos los datos del formulario.
$identifier = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// --- Validación de Entrada ---
if (empty($identifier) || empty($password)) {
    $_SESSION['error_message'] = "El identificador y la contraseña no pueden estar vacíos.";
    header('Location: ' . BASE_URL . 'index.php?p=login_form');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :identifier");
    $stmt->execute(['identifier' => $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $password_verified = false;
    if ($user && !empty($user['hash_password']) && !empty($user['salt'])) {
        if (!defined('PEPPER')) {
            define('PEPPER', trim(file_get_contents(ROOT_PATH . '/config/pepper.key')));
        }
        
        $password_peppered = hash_hmac("sha256", $password, PEPPER);
        $password_to_verify = $password_peppered . $user['salt'];
        
        if (password_verify($password_to_verify, $user['hash_password'])) {
            $password_verified = true;
        }
    }

    if ($user && $password_verified) {
        if ($user['estado_cuenta'] !== 'activo') {
            log_system_event("Intento de login fallido", ['usuario' => $identifier, 'motivo' => 'Estado no activo', 'estado' => $user['estado_cuenta']]);
            $_SESSION['error_message'] = "Tu cuenta se encuentra en estado '{$user['estado_cuenta']}'. Por favor, contacta con un administrador.";
            header('Location: ' . BASE_URL . 'index.php?p=login_form');
            exit;
        }
        
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['apellido'] = $user['apellido'];
        $_SESSION['email'] = $user['email'];
        // CORRECCIÓN: Se utiliza 'rango' en lugar de 'rol'.
        $_SESSION['rango'] = $user['rango'];
        $_SESSION['fecha_registro'] = $user['fecha_registro'];
        $_SESSION['avatar_seleccionado'] = $user['avatar_seleccionado'];
        $_SESSION['avatar_google'] = $user['avatar_google'];
        $_SESSION['estado_cuenta'] = $user['estado_cuenta'];
        $_SESSION['tema'] = $user['tema'];

        if (empty($user['hash_password']) && $user['ignorar_unificacion_pwd'] == 0) {
            $_SESSION['password_creation_required'] = true;
        } else {
            unset($_SESSION['password_creation_required']);
        }
        
        log_system_event("Login exitoso para usuario ID: {$user['id_usuario']} ({$identifier}).");

        header('Location: ' . BASE_URL . 'index.php?p=perfil');
        exit;

    } else {
        log_system_event("Credenciales inválidas para el intento de login: '{$identifier}'.");
        $_SESSION['error_message'] = "Correo electrónico o contraseña incorrectos.";
        header('Location: ' . BASE_URL . 'index.php?p=login_form');
        exit;
    }

} catch (PDOException $e) {
    log_system_event("Error de base de datos en procesar_login.php: " . $e->getMessage(), true);
    echo "[DEBUG BROWSER] Error de base de datos en procesar_login.php: " . $e->getMessage();
    $_SESSION['error_message'] = "Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.";
    exit;
}