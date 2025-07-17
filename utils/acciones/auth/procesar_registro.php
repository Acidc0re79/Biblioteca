<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

// Recogemos y saneamos los datos del formulario.
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
// CORRECCIÓN: Añadimos la fecha de nacimiento.
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

// --- VALIDACIONES ---
// CORRECCIÓN: Añadimos fecha_nacimiento a la validación.
if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($fecha_nacimiento)) {
    $_SESSION['error_message'] = "Todos los campos del formulario son obligatorios.";
    header('Location: ' . BASE_URL . 'index.php?p=login_form');
    exit;
}
// ... (resto de validaciones sin cambios)
if ($password !== $password_confirm) { /* ... */ }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { /* ... */ }
if (strlen($password) < 8) { /* ... */ }

// --- PROCESAMIENTO EN LA BASE DE DATOS ---
try {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "El correo electrónico ya está en uso.";
        header('Location: ' . BASE_URL . 'index.php?p=login_form');
        exit;
    }

    if (!defined('PEPPER')) {
        define('PEPPER', trim(file_get_contents(ROOT_PATH . '/config/pepper.key')));
    }
    $salt = bin2hex(random_bytes(16));
    $password_peppered = hash_hmac("sha256", $password, PEPPER);
    $hash_password = password_hash($password_peppered . $salt, PASSWORD_DEFAULT);

    // CORRECCIÓN: Se añade fecha_nacimiento a la consulta INSERT.
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, apellido, email, hash_password, salt, rango, estado_cuenta, proveedor_oauth, fecha_nacimiento)
         VALUES (?, ?, ?, ?, ?, 'lector', 'pendiente', 'local', ?)"
    );
    
    // CORRECCIÓN: Se añade la variable $fecha_nacimiento al array de ejecución.
    if ($stmt->execute([$nombre, $apellido, $email, $hash_password, $salt, $fecha_nacimiento])) {
        $new_user_id = $pdo->lastInsertId();
        log_system_event("Nuevo registro de usuario local exitoso. ID: {$new_user_id}, Email: {$email}.");
        
        $_SESSION['success_message'] = "¡Registro completado! Tu cuenta está pendiente de aprobación por un administrador.";
        // CORRECIÓN: Redirigimos al index, que ahora mostrará la página 'main' con el mensaje.
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    } else {
        $_SESSION['error_message'] = "No se pudo completar el registro. Inténtalo de nuevo.";
        header('Location: ' . BASE_URL . 'index.php?p=login_form');
        exit;
    }

} catch (PDOException $e) {
    log_system_event("Error de base de datos en procesar_registro.php: " . $e->getMessage(), true);
    $_SESSION['error_message'] = "Ocurrió un error en el servidor. Por favor, inténtalo de nuevo más tarde.";
    header('Location: ' . BASE_URL . 'index.php?p=login_form');
    exit;
}