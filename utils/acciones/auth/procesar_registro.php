<?php
// /utils/acciones/auth/procesar_registro.php (Versión Autosuficiente y Final)

// ✅ INICIALIZACIÓN PROPIA: El script ahora llama a init.php por sí mismo.
// Usamos la constante ROOT_PATH que fue definida por el form-handler.
require_once ROOT_PATH . '/config/init.php';

// Ahora que hemos llamado a init.php, la variable $pdo existe y podemos usarla.
$pepper_path = ROOT_PATH . '/config/pepper.key';
if (!file_exists($pepper_path)) {
    die('Error de configuración: No se encuentra el archivo pepper.key.');
}
$pepper = trim(file_get_contents($pepper_path));

// --- FUNCIONES DE AYUDA ---
function generarSalt($longitud = 16) {
    return bin2hex(random_bytes($longitud));
}
function generarToken() {
    return bin2hex(random_bytes(32));
}

// --- VALIDACIÓN ---
$campos_obligatorios = ['nombre', 'apellido', 'fecha_nac', 'email', 'password', 'confirmar_password'];
foreach ($campos_obligatorios as $campo) {
    if (empty($_POST[$campo])) {
        header("Location: /index.php?pagina=login_form&error=registro_campos");
        exit;
    }
}

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$fecha_nac = $_POST['fecha_nac'];
$email = strtolower(trim($_POST['email']));
$password = $_POST['password'];
$confirmar_password = $_POST['confirmar_password'];

if ($password !== $confirmar_password) {
    header("Location: /index.php?pagina=login_form&error=registro_password");
    exit;
}

// --- LÓGICA DE BASE DE DATOS ---
try {
    // Comprobar si el email ya existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: /index.php?pagina=login_form&error=registro_email");
        exit;
    }

    // Crear hash de la contraseña
    $salt = generarSalt();
    $password_peppered = hash_hmac("sha256", $password, $pepper);
    $hash = password_hash($password_peppered . $salt, PASSWORD_DEFAULT);

    // Preparar e insertar el nuevo usuario
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, apellido, fecha_nacimiento, email, hash_password, salt, estado_cuenta, token_verificacion, fecha_token, proveedor_oauth, rango) 
        VALUES (:nombre, :apellido, :fecha_nac, :email, :hash, :salt, 'pendiente', :token, NOW(), 'local', 'lector')"
    );
    $stmt->execute([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'fecha_nac' => $fecha_nac,
        'email' => $email,
        'hash' => $hash,
        'salt' => $salt,
        'token' => generarToken()
    ]);

    header("Location: /index.php?pagina=login_form&registro=ok");
    exit;

} catch (PDOException $e) {
    error_log("Error al registrar usuario: " . $e->getMessage());
    header("Location: /index.php?pagina=login_form&error=db");
    exit;
}
?>