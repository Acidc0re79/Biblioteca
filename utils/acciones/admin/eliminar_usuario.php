<?php
// /utils/acciones/admin/eliminar_usuario.php
require_once ROOT_PATH . '/config/init.php';

// Verificación de seguridad: solo un admin puede ejecutar esto.
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'] ?? '', ['administrador'])) {
    die('Acceso no autorizado.');
}

// Obtenemos el ID del usuario a eliminar
$id_usuario_a_eliminar = $_POST['id_usuario'] ?? null;

if (!$id_usuario_a_eliminar) {
    die('Error: No se ha especificado un ID de usuario para eliminar.');
}

// Por seguridad, un administrador no puede eliminarse a sí mismo.
if ($id_usuario_a_eliminar == $_SESSION['usuario_id']) {
    die('Error: No puedes eliminar tu propia cuenta de administrador desde aquí.');
}

try {
    // Iniciamos una transacción. Si algo falla, todo se revierte.
    $pdo->beginTransaction();

    // 1. Eliminar archivos físicos (avatares generados)
    $stmt_avatares = $pdo->prepare("SELECT nombre_archivo FROM usuarios_avatares WHERE id_usuario = ?");
    $stmt_avatares->execute([$id_usuario_a_eliminar]);
    $avatares_a_borrar = $stmt_avatares->fetchAll(PDO::FETCH_COLUMN);

    foreach ($avatares_a_borrar as $nombre_archivo) {
        $ruta_full = ROOT_PATH . '/public/assets/img/avatars/users/' . $nombre_archivo;
        $ruta_thumb = ROOT_PATH . '/public/assets/img/avatars/thumbs/users/' . $nombre_archivo;
        if (file_exists($ruta_full)) { @unlink($ruta_full); }
        if (file_exists($ruta_thumb)) { @unlink($ruta_thumb); }
    }

    // 2. Eliminar registros de la base de datos en cascada
    // (Gracias a las claves foráneas con ON DELETE CASCADE, solo necesitamos borrar de la tabla principal.
    // Si no las tuviéramos, tendríamos que borrar de usuarios_avatares, usuarios_insignias, etc., primero)
    $stmt_delete_user = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt_delete_user->execute([$id_usuario_a_eliminar]);

    // 3. Si todo fue bien, confirmamos la transacción
    $pdo->commit();

    // Redirigimos de vuelta a la lista con un mensaje de éxito.
    header("Location: /admin/usuarios.php?exito=eliminacion");
    exit;

} catch (PDOException $e) {
    // Si algo falla, revertimos todos los cambios.
    $pdo->rollBack();
    error_log("Error al eliminar usuario: " . $e->getMessage());
    die("Error de base de datos al intentar eliminar el usuario.");
}
?>