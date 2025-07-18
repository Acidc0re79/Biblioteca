<?php
// /utils/helpers.php (Versión Confirmada)

/**
 * Obtiene la URL completa y segura de la imagen de avatar de un usuario.
 *
 * @param string|null $avatar_filename El nombre del archivo del avatar desde la BD.
 * @param string $email El email del usuario, necesario para el fallback a Gravatar.
 * @return string La URL completa y segura de la imagen del avatar.
 */
function get_avatar_url($avatar_filename, $email)
{
    // 1. Si el usuario tiene un avatar personalizado subido/generado.
    if (!empty($avatar_filename)) {
        // AVATARS_URL es una constante definida en init.php.
        return AVATARS_URL . '/' . htmlspecialchars($avatar_filename);
    }

    // 2. Si no, usamos Gravatar como respaldo.
    $email_limpio = strtolower(trim($email));
    $gravatar_hash = md5($email_limpio);
    // d=mp significa "mystery person". s=150 pide una imagen de 150px.
    return "https://www.gravatar.com/avatar/" . $gravatar_hash . "?d=mp&s=150";
}

/**
 * Guarda datos binarios de una imagen en un archivo, crea una miniatura
 * y devuelve las rutas.
 *
 * @param string $datosImagenBinarios Los datos crudos de la imagen.
 * @param string $nombreArchivo El nombre de archivo único para guardar la imagen.
 * @return array ['success' => bool, 'data' => 'mensaje_error' o array con rutas]
 */
function guardarYCrearThumbnail($datosImagenBinarios, $nombreArchivo) {
    // Definimos las rutas de guardado
    $rutaCompleta = ROOT_PATH . '/public/assets/img/avatars/users/' . $nombreArchivo;
    $rutaThumbnail = ROOT_PATH . '/public/assets/img/avatars/thumbs/users/' . $nombreArchivo;
    $urlCompleta = BASE_URL . 'assets/img/avatars/users/' . $nombreArchivo;

    // Guardamos la imagen principal
    if (!file_put_contents($rutaCompleta, $datosImagenBinarios)) {
        log_ia_event('Error crítico al guardar la imagen principal.', ['ruta' => $rutaCompleta]);
        return ['success' => false, 'data' => 'Error del servidor al guardar la imagen.'];
    }

    // Creamos la miniatura desde el archivo que acabamos de guardar
    try {
        $imagenOriginal = imagecreatefromstring($datosImagenBinarios);
        if ($imagenOriginal === false) throw new Exception("GD no pudo procesar los datos de la imagen.");

        $anchoOriginal = imagesx($imagenOriginal);
        $altoOriginal = imagesy($imagenOriginal);
        $anchoThumbnail = 150; // Ancho fijo para la miniatura
        $altoThumbnail = floor($altoOriginal * ($anchoThumbnail / $anchoOriginal));

        $thumbnail = imagecreatetruecolor($anchoThumbnail, $altoThumbnail);
        imagecopyresampled($thumbnail, $imagenOriginal, 0, 0, 0, 0, $anchoThumbnail, $altoThumbnail, $anchoOriginal, $altoOriginal);

        // Guardamos el thumbnail como PNG
        imagepng($thumbnail, $rutaThumbnail);

        // Liberamos memoria
        imagedestroy($imagenOriginal);
        imagedestroy($thumbnail);

        return [
            'success' => true,
            'data' => [
                'ruta_completa' => $rutaCompleta,
                'ruta_thumbnail' => $rutaThumbnail,
                'url_completa' => $urlCompleta
            ]
        ];
    } catch (Exception $e) {
        log_ia_event('Error crítico al crear el thumbnail.', ['error' => $e->getMessage()]);
        return ['success' => false, 'data' => 'Error del servidor al procesar la imagen.'];
    }
}