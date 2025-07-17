<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
// Asume que es llamado por un AJAX Handler.

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

$avatar_url = $payload['avatar_url'] ?? null;
$id_usuario = $_SESSION['user_id'];

if (empty($avatar_url)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se proporcionó la URL del avatar.']);
    exit;
}

// Medida de seguridad: Validamos que la ruta del avatar sea segura.
$avatar_final_db = basename($avatar_url); // Guardamos solo el nombre del archivo.

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET avatar_actual = ? WHERE id_usuario = ?");
    $stmt->execute([$avatar_final_db, $id_usuario]);

    $_SESSION['avatar_actual'] = $avatar_final_db; // Actualizamos la sesión.

    echo json_encode(['success' => true, 'message' => 'Avatar actualizado.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
}