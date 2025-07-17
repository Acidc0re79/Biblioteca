<?php
// Incluimos la inicialización para tener sesión y $pdo
require_once dirname(__DIR__, 2) . '/config/init.php';

// 1. Verificar que el usuario está logueado y que la petición es POST
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /index.php?pagina=perfil&error=invalido");
    exit;
}

// 2. Recoger y sanear los datos del formulario
$id_usuario = $_SESSION['usuario_id'];
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$tema_elegido = trim($_POST['tema'] ?? 'default');

// 3. Validación básica (puedes añadir más si quieres)
if (empty($nombre) || empty($apellido)) {
    header("Location: /index.php?pagina=perfil&error=campos");
    exit;
}

// 4. Verificar que el tema elegido existe en la base de datos (medida de seguridad)
$stmt_tema = $pdo->prepare("SELECT COUNT(*) FROM temas WHERE directorio = ? AND activo = 1");
$stmt_tema->execute([$tema_elegido]);
if ($stmt_tema->fetchColumn() == 0) {
    // Si el tema no es válido, se usa el default para no romper el sitio
    $tema_elegido = 'default';
}

try {
    // 5. Preparar y ejecutar la actualización en la base de datos
    $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, descripcion = :descripcion, tema = :tema WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'descripcion' => $descripcion,
        'tema' => $tema_elegido,
        'id_usuario' => $id_usuario
    ]);

    // 6. Actualizar la sesión con los nuevos datos
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellido'] = $apellido;
    $_SESSION['descripcion'] = $descripcion;
    $_SESSION['tema'] = $tema_elegido;

    // 7. Redirigir de vuelta al perfil con un mensaje de éxito
    header("Location: /index.php?pagina=perfil&actualizado=ok");
    exit;

} catch (PDOException $e) {
    error_log("Error al actualizar perfil: " . $e->getMessage());
    header("Location: /index.php?pagina=perfil&error=db");
    exit;
}