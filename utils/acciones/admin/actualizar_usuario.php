<?php
// /utils/acciones/admin/actualizar_usuario.php
require_once ROOT_PATH . '/config/init.php';

// Verificación de seguridad: solo un admin puede ejecutar esto.
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'] ?? '', ['administrador'])) {
    die('Acceso no autorizado.');
}

// Recogemos los datos del formulario
$id_usuario_a_editar = $_POST['id_usuario'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$rango = $_POST['rango'] ?? 'lector';
$estado_cuenta = $_POST['estado_cuenta'] ?? 'pendiente';
$puntos = $_POST['puntos'] ?? 0;
$resetear_intentos = isset($_POST['resetear_intentos']);
$nueva_password = $_POST['password'] ?? '';

if (!$id_usuario_a_editar) {
    die('Error: No se ha especificado un ID de usuario.');
}

try {
    // Construimos la consulta base
    $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, rango = ?, estado_cuenta = ?, puntos = ?";
    $params = [$nombre, $apellido, $rango, $estado_cuenta, $puntos];

    // Si se marcó la casilla de resetear intentos
    if ($resetear_intentos) {
        $sql .= ", intentos_avatar = 0";
    }

    // Si se proporcionó una nueva contraseña
    if (!empty($nueva_password)) {
        $pepper = trim(file_get_contents(ROOT_PATH . '/config/pepper.key'));
        $salt = bin2hex(random_bytes(16));
        $password_peppered = hash_hmac("sha256", $nueva_password, $pepper);
        $hash = password_hash($password_peppered . $salt, PASSWORD_DEFAULT);

        $sql .= ", hash_password = ?, salt = ?";
        $params[] = $hash;
        $params[] = $salt;
    }

    // Finalizamos la consulta
    $sql .= " WHERE id_usuario = ?";
    $params[] = $id_usuario_a_editar;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Redirigimos de vuelta a la lista de usuarios con un mensaje de éxito.
    header("Location: /admin/usuarios.php?exito=edicion");
    exit;

} catch (PDOException $e) {
    error_log("Error al actualizar usuario: " . $e->getMessage());
    die("Error de base de datos al intentar actualizar el usuario.");
}
?>