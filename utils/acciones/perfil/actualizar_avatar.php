<?php
// /public/users/actualizar_avatar.php

require_once dirname(__DIR__, 2) . '/config/init.php';

header('Content-Type: application/json');

// --- FUNCIÓN PARA CREAR MINIATURAS ---
function crearThumbnail($source_path, $destination_path, $width = 150) {
    $image_info = getimagesize($source_path);
    
    if ($image_info === false) {
        error_log("ERROR THUMBNAIL: getimagesize() falló o el archivo no es una imagen válida en: " . $source_path);
        throw new Exception("El archivo de origen no es una imagen válida o está corrupta: " . basename($source_path));
    }

    $source_width = $image_info[0];
    $source_height = $image_info[1];
    $mime = $image_info['mime'] ?? null;

    if (!$source_width || !$source_height || $mime === null) {
        error_log("ERROR THUMBNAIL: No se pudieron obtener dimensiones o tipo MIME de la imagen en: " . $source_path . ". Tipo detectado: " . ($mime ?? 'null'));
        throw new Exception("No se pudo detectar el tipo MIME de la imagen o sus dimensiones. Archivo: " . basename($source_path));
    }
    
    $source_image = null;
    switch ($mime) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            error_log("ERROR THUMBNAIL: Tipo de imagen no soportado para la creación de miniatura: {$mime}. Ruta: {$source_path}");
            throw new Exception("Tipo de imagen ('{$mime}') no soportado para la creación de miniatura.");
    }

    if (!$source_image) {
        error_log("ERROR THUMBNAIL: No se pudo crear el recurso de imagen de origen (GD Library) desde " . $source_path . ". Puede ser un archivo corrupto.");
        throw new Exception("Error al procesar la imagen con la librería GD. Archivo: " . basename($source_path));
    }

    $thumb_height = ($source_height / $source_width) * $width;
    $thumb = imagecreatetruecolor($width, $thumb_height);
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);
    imagecopyresampled($thumb, $source_image, 0, 0, 0, 0, $width, $thumb_height, $source_width, $source_height);
    $success = imagepng($thumb, $destination_path, 9);
    
    if (!$success) {
        error_log("ERROR THUMBNAIL: No se pudo guardar la imagen en la ruta de destino de la miniatura: " . $destination_path);
        throw new Exception("No se pudo guardar la miniatura. Verifique permisos de escritura en la carpeta de destino: " . basename(dirname($destination_path)));
    }
    
    imagedestroy($source_image);
    imagedestroy($thumb);
    return true;
}

// --- Inicio de la Lógica Principal del Script ---
try {
    if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
        exit;
    }
    $id_usuario = $_SESSION['usuario_id'];

    global $pdo; // Aseguramos que PDO esté disponible
// La variable $data ya nos la proporciona ajax-handler.php
    $avatar_path = $data['avatar_path'] ?? null;
    $es_generado_por_ia = $data['es_generado'] ?? false;

    if (!$avatar_path) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se ha proporcionado un avatar.']);
        exit;
    }

    // Iniciar la transacción de la base de datos.
    $pdo->beginTransaction();

    $avatar_para_db = '';
    $url_final_avatar = '';
    
    if ($es_generado_por_ia) {
        // --- PROCESO PARA AVATAR GENERADO POR IA ---
        $ruta_temporal_completa = ROOT_PATH . '/public' . $avatar_path;

        if (!file_exists($ruta_temporal_completa) || !is_readable($ruta_temporal_completa)) {
            throw new Exception('El archivo temporal de la imagen generada por IA no se encontró o no es legible.');
        }

        $nombre_archivo_unico = 'user_' . $id_usuario . '_' . time() . '.png';
        $ruta_guardado_full = ROOT_PATH . '/public/assets/img/avatars/users/' . $nombre_archivo_unico;
        $ruta_guardado_thumb = ROOT_PATH . '/public/assets/img/avatars/thumbs/users/' . $nombre_archivo_unico;

        // Asegurar que los directorios de destino existan
        $dir_full = dirname($ruta_guardado_full);
        $dir_thumb = dirname($ruta_guardado_thumb);
        if (!is_dir($dir_full)) { if (!mkdir($dir_full, 0775, true)) throw new Exception('No se pudo crear directorio avatares completos.'); }
        if (!is_dir($dir_thumb)) { if (!mkdir($dir_thumb, 0775, true)) throw new Exception('No se pudo crear directorio miniaturas.'); }

        // Copiar la imagen temporal a su destino final.
        if (!copy($ruta_temporal_completa, $ruta_guardado_full)) {
            throw new Exception('No se pudo copiar el archivo temporal. Verifique permisos de escritura en: ' . $dir_full);
        }

        // Eliminar el archivo temporal original una vez que ha sido copiado exitosamente.
        if (!@unlink($ruta_temporal_completa)) {
            error_log("ADVERTENCIA: No se pudo eliminar el archivo temporal original: " . $ruta_temporal_completa);
        }

        // Crear la miniatura de la imagen recién copiada.
        if (!crearThumbnail($ruta_guardado_full, $ruta_guardado_thumb)) {
            throw new Exception('Fallo al crear la miniatura del avatar. Revise logs para detalles.');
        }

        // Insertar registro del avatar en usuarios_avatares.
        $stmt_insert = $pdo->prepare("INSERT INTO usuarios_avatares (id_usuario, nombre_archivo) VALUES (?, ?)");
        if (!$stmt_insert->execute([$id_usuario, $nombre_archivo_unico])) {
            throw new Exception('Error al registrar el avatar en la base de datos de creaciones: ' . implode(" | ", $stmt_insert->errorInfo()));
        }

        // ESTE ES EL PUNTO CLAVE: CONTAR EL INTENTO SOLO SI TODO LO ANTERIOR FUE EXITOSO
        $stmt_increment_intentos = $pdo->prepare("UPDATE usuarios SET intentos_avatar = intentos_avatar + 1, fecha_ultimo_intento = NOW() WHERE id_usuario = ?");
        if (!$stmt_increment_intentos->execute([$id_usuario])) {
            throw new Exception('Error al incrementar los intentos de avatar del usuario.');
        }

        $avatar_para_db = $nombre_archivo_unico;
        $url_final_avatar = '/assets/img/avatars/users/' . $nombre_archivo_unico;

    } else {
        // --- PROCESO PARA AVATAR PREDETERMINADO O DE GOOGLE ---
        $directorio_base = ROOT_PATH . '/public';
        $ruta_fisica_avatar = realpath($directorio_base . $avatar_path);
        if (!$ruta_fisica_avatar || !str_starts_with($ruta_fisica_avatar, $directorio_base . '/assets/img/avatars')) {
             throw new Exception('Ruta de avatar prediseñado no válida o fuera de rango.');
        }
        $avatar_para_db = str_starts_with($avatar_path, '/assets/img/avatars/users/') ? basename($avatar_path) : $avatar_path;
        $url_final_avatar = $avatar_path;
    }

    // Actualizar el avatar seleccionado en la tabla de usuarios.
    $stmt_update = $pdo->prepare("UPDATE usuarios SET avatar_seleccionado = ? WHERE id_usuario = ?");
    if (!$stmt_update->execute([$avatar_para_db, $id_usuario])) {
        throw new Exception('Error al actualizar el avatar en la tabla de usuarios: ' . implode(" | ", $stmt_update->errorInfo()));
    }
    // Actualizar la sesión del usuario.
    $_SESSION['avatar_seleccionado'] = $avatar_para_db;

    // Obtener los intentos actualizados para la respuesta al frontend (solo para avatares generados por IA)
    $intentos_restantes_para_frontend = $_SESSION['puntos'] ?? 0; // Valor por defecto si no es IA
    if ($es_generado_por_ia) {
        $stmt_intentos_actualizados = $pdo->prepare("SELECT intentos_avatar FROM usuarios WHERE id_usuario = ?");
        $stmt_intentos_actualizados->execute([$id_usuario]);
        $intentos_utilizados_ahora = $stmt_intentos_actualizados->fetchColumn();
        $total_permitidos = CONFIG_SITIO['intentos_avatar_iniciales'] ?? 50; // Obtener el total configurable
        $intentos_restantes_para_frontend = max(0, $total_permitidos - $intentos_utilizados_ahora);
    }

    // Confirmar la transacción en la base de datos.
    $pdo->commit();

    // Preparar y enviar la respuesta JSON exitosa.
    echo json_encode([
        'success' => true,
        'new_full_avatar_url' => $url_final_avatar,
        'new_thumb_avatar_url' => str_replace('/users/', '/thumbs/users/', $url_final_avatar),
        'intentos_restantes' => $es_generado_por_ia ? $intentos_restantes_para_frontend : ($_SESSION['intentos_avatar'] ?? 0), // Devolver intentos si es IA
        'message' => 'Avatar actualizado con éxito.'
    ]);
    exit;

} catch (Exception $e) {
    // Si ocurre un error, revertimos la transacción.
    $pdo->rollBack();
    error_log("ERROR FATAL en actualizar_avatar.php: " . $e->getMessage()); // Mantenemos el log de errores graves
    
    // Enviar respuesta JSON de error.
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al procesar el avatar: ' . $e->getMessage()]);
    exit;
}
?>