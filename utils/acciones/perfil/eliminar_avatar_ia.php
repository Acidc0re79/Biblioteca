<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
// Este script asume que es llamado por un AJAX Handler.

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

// El payload (los datos JSON) es proporcionado por el ajax-handler.php.
$id_avatar_a_eliminar = $payload['avatar_id'] ?? null;
$id_usuario = $_SESSION['user_id'];

if (empty($id_avatar_a_eliminar)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se proporcionó el ID del avatar a eliminar.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificamos que el avatar pertenece al usuario y obtenemos el nombre del archivo.
    $stmt = $pdo->prepare("SELECT nombre_archivo FROM usuarios_avatares WHERE id = :id_avatar AND id_usuario = :id_usuario");
    $stmt->execute(['id_avatar' => $id_avatar_a_eliminar, 'id_usuario' => $id_usuario]);
    $avatar = $stmt->fetch();

    if (!$avatar) {
        throw new Exception('No tienes permiso para eliminar este avatar o ya no existe.');
    }

    $nombre_archivo = $avatar['nombre_archivo'];

    // 2. Si el avatar a borrar era el seleccionado, volvemos al por defecto.
    if (isset($_SESSION['avatar_actual']) && $_SESSION['avatar_actual'] === $nombre_archivo) {
        $stmt_update_user = $pdo->prepare("UPDATE usuarios SET avatar_actual = NULL WHERE id_usuario = ?");
        $stmt_update_user->execute([$id_usuario]);
        $_SESSION['avatar_actual'] = null; // Actualizamos la sesión.
    }

    // 3. Eliminar el registro de la base de datos.
    $stmt_delete = $pdo->prepare("DELETE FROM usuarios_avatares WHERE id = ?");
    $stmt_delete->execute([$id_avatar_a_eliminar]);

    // 4. Eliminar los archivos físicos (imagen principal y miniatura).
    $ruta_original = ROOT_PATH . 'public/uploads/avatars/' . $nombre_archivo;
    // Asumimos que no hay miniaturas para avatares de IA o se manejan por separado.
    if (file_exists($ruta_original)) {
        unlink($ruta_original);
    }
    
    // Si todo fue bien, confirmamos la transacción.
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Avatar eliminado correctamente.']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error al eliminar avatar: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor al eliminar el avatar.']);
}