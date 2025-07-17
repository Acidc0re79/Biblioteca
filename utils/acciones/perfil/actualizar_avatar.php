<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
// Este script asume que un manejador (ej. ajax-handler.php) ya ha:
// 1. Iniciado la sesión y cargado la configuración con init.php.
// 2. Creado la conexión $pdo a la base de datos.
// 3. Verificado que el usuario está logueado.

// --- Verificación de Seguridad Adicional ---
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'message' => 'Acceso denegado: debes iniciar sesión para cambiar tu avatar.']);
    exit;
}

// Verificamos si se subió un archivo y si no hubo errores en la subida.
if (!isset($_FILES['avatarFile']) || $_FILES['avatarFile']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    http_response_code(400); // Petición incorrecta
    $errorMessage = 'No se ha seleccionado ningún archivo o hubo un error en la subida.';
    // Podemos dar mensajes más específicos según el código de error
    switch ($_FILES['avatarFile']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage = 'El archivo es demasiado grande.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage = 'No se ha seleccionado ningún archivo.';
            break;
    }
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    exit;
}

$file = $_FILES['avatarFile'];
$userId = $_SESSION['user_id'];

// --- Validación del Archivo ---
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
$file_mime_type = mime_content_type($file['tmp_name']);

if (!in_array($file_mime_type, $allowed_mime_types)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Solo se aceptan JPG, PNG y GIF.']);
    exit;
}

// --- Procesamiento y Guardado del Archivo ---
// Generamos un nombre de archivo único para evitar colisiones y problemas de caché.
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$avatarFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
$avatarPath = AVATARS_PATH . $avatarFileName; // AVATARS_PATH debe estar definida en init.php

if (move_uploaded_file($file['tmp_name'], $avatarPath)) {
    try {
        $pdo->beginTransaction();

        // Guardamos la referencia del nuevo avatar en la tabla de avatares.
        $stmt_insert = $pdo->prepare("INSERT INTO usuarios_avatares (id_usuario, nombre_archivo, origen) VALUES (?, ?, 'subido')");
        $stmt_insert->execute([$userId, $avatarFileName]);
        
        // Actualizamos la tabla principal de usuarios con el nuevo avatar activo.
        $stmt_update = $pdo->prepare("UPDATE usuarios SET avatar_actual = ? WHERE id_usuario = ?");
        $stmt_update->execute([$avatarFileName, $userId]);
        
        $pdo->commit();

        // Actualizamos la sesión del usuario.
        $_SESSION['avatar_actual'] = $avatarFileName;

        // Respondemos con éxito y la URL del nuevo avatar.
        header('Content-Type: application/json');
        $avatarUrl = BASE_URL . 'uploads/avatars/' . $avatarFileName; // BASE_URL definida en init.php
        echo json_encode(['success' => true, 'avatarUrl' => $avatarUrl, 'message' => 'Avatar actualizado con éxito.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        // Si falla la base de datos, eliminamos el archivo que acabamos de subir.
        if (file_exists($avatarPath)) { unlink($avatarPath); }
        error_log('Error de BD en actualizar_avatar.php: ' . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al guardar la información del avatar.']);
    }
} else {
    // Esto suele ser un problema de permisos en la carpeta del servidor.
    error_log("Error de permisos: No se pudo mover el archivo subido a {$avatarPath}");
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor al guardar el archivo.']);
}