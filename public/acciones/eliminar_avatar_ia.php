<?php
// public/acciones/eliminar_avatar_ia.php

require_once dirname(__DIR__, 2) . '/config/init.php';
require_once ROOT_PATH . '/utils/debug_helper.php';

header('Content-Type: application/json');

syslr_debug_log("Diario de eliminar_avatar_ia.php - " . date('Y-m-d H:i:s'));

// 1. Verificar sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Inicia sesión.']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // 2. Obtener y validar datos de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    $avatar_id = filter_var($data['avatar_id'] ?? null, FILTER_VALIDATE_INT);
    $nombre_archivo_solicitado = htmlspecialchars(strip_tags(trim($data['nombre_archivo'] ?? '')));

    if (!$avatar_id || empty($nombre_archivo_solicitado)) {
        throw new Exception("Datos de avatar inválidos para la eliminación.");
    }

    // Iniciar transacción para asegurar la integridad de la base de datos y archivos
    $pdo->beginTransaction();

    // 3. Verificar que el avatar pertenece al usuario logueado y obtener su información
    $stmt = $pdo->prepare("SELECT nombre_archivo FROM usuarios_avatares WHERE id = :avatar_id AND id_usuario = :id_usuario LIMIT 1");
    $stmt->execute([
        'avatar_id' => $avatar_id,
        'id_usuario' => $id_usuario
    ]);
    $avatar_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$avatar_info) {
        throw new Exception("Avatar no encontrado o no pertenece a este usuario.");
    }

    $nombre_archivo_db = $avatar_info['nombre_archivo'];

    // 4. Doble verificación del nombre del archivo para seguridad extrema
    if ($nombre_archivo_db !== $nombre_archivo_solicitado) {
        throw new Exception("Discrepancia en el nombre del archivo. Operación abortada por seguridad.");
    }

    // 5. Verificar si el avatar a eliminar es el avatar principal actualmente seleccionado por el usuario
    $stmt_check_current = $pdo->prepare("SELECT avatar_seleccionado FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1");
    $stmt_check_current->execute(['id_usuario' => $id_usuario]);
    $usuario_actual = $stmt_check_current->fetch(PDO::FETCH_ASSOC);

    if ($usuario_actual && $usuario_actual['avatar_seleccionado'] === $nombre_archivo_db) {
        // Si el avatar a borrar es el actualmente seleccionado, lo cambiamos a default
        $stmt_update_user_avatar = $pdo->prepare("UPDATE usuarios SET avatar_seleccionado = '/assets/img/default_avatar.png' WHERE id_usuario = :id_usuario");
        $stmt_update_user_avatar->execute(['id_usuario' => $id_usuario]);
    }

    // 6. Eliminar el registro de la base de datos
    $stmt_delete = $pdo->prepare("DELETE FROM usuarios_avatares WHERE id = :avatar_id AND id_usuario = :id_usuario");
    $stmt_delete->execute([
        'avatar_id' => $avatar_id,
        'id_usuario' => $id_usuario
    ]);

    if ($stmt_delete->rowCount() === 0) {
        throw new Exception("No se pudo eliminar el registro del avatar de la base de datos.");
    }

    // 7. Eliminar los archivos físicos (imagen principal y miniatura)
    $ruta_imagen_original = ROOT_PATH . '/public/assets/img/avatars/users/' . $nombre_archivo_db;
    $ruta_imagen_thumb = ROOT_PATH . '/public/assets/img/avatars/thumbs/users/' . $nombre_archivo_db;

    $deleted_original = false;
    $deleted_thumb = false;

    if (file_exists($ruta_imagen_original)) {
        if (unlink($ruta_imagen_original)) {
            $deleted_original = true;
        } else {
            syslr_debug_log("ERROR: No se pudo eliminar el archivo original: " . $ruta_imagen_original, true);
            // No lanzamos excepción aquí para no revertir la BD si el archivo ya no existe o hay un problema de permisos
            // pero lo logueamos.
        }
    } else {
        syslr_debug_log("Advertencia: El archivo original no existe: " . $ruta_imagen_original, true);
        $deleted_original = true; // Consideramos que está "eliminado" si ya no existe
    }

    if (file_exists($ruta_imagen_thumb)) {
        if (unlink($ruta_imagen_thumb)) {
            $deleted_thumb = true;
        } else {
            syslr_debug_log("ERROR: No se pudo eliminar el archivo thumb: " . $ruta_imagen_thumb, true);
            // Igual que arriba, logueamos pero no revertimos la BD.
        }
    } else {
        syslr_debug_log("Advertencia: El archivo thumb no existe: " . $ruta_imagen_thumb, true);
        $deleted_thumb = true; // Consideramos que está "eliminado" si ya no existe
    }

    // Si la BD se actualizó y al menos un archivo se intentó eliminar (o no existía), confirmamos la transacción
    $pdo->commit();

    // 8. Actualizar el contador de intentos del usuario (se "recupera" un intento)
    $stmt_update_intentos = $pdo->prepare("UPDATE usuarios SET intentos_avatar = intentos_avatar - 1 WHERE id_usuario = :id_usuario");
    $stmt_update_intentos->execute(['id_usuario' => $id_usuario]);


    echo json_encode(['success' => true, 'message' => 'Avatar eliminado con éxito.']);

} catch (Exception $e) {
    // Si algo falla, revertir la transacción
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    syslr_debug_log("ERROR en eliminar_avatar_ia.php: " . $e->getMessage(), true);
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el avatar: ' . $e->getMessage()]);
}
?>