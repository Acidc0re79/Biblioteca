<?php
// /public/ajax-handler.php (Versión con las nuevas acciones)

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/init.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['action'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error: Acción no especificada.']);
    exit;
}

$action = $input['action'];
$payload = $input['payload'] ?? [];

// Lista actualizada de acciones permitidas
$allowed_actions = [
    // Perfil
    'actualizar_avatar'       => ROOT_PATH . '/utils/acciones/perfil/actualizar_avatar.php',
    'eliminar_avatar_ia'      => ROOT_PATH . '/utils/acciones/perfil/eliminar_avatar_ia.php',
    'crear_password'          => ROOT_PATH . '/utils/acciones/perfil/crear_password.php',
    'ignorar_unificacion'     => ROOT_PATH . '/utils/acciones/perfil/ignorar_unificacion.php',
    'limpiar_flag_sesion'     => ROOT_PATH . '/utils/acciones/perfil/limpiar_flag_sesion.php',
    
    // IA
    'generar_prompt_gemini'   => ROOT_PATH . '/utils/acciones/ia/generar_prompt_gemini.php',
    'generar_avatar_ia'       => ROOT_PATH . '/utils/acciones/ia/generar_avatar_ia.php'
];

if (array_key_exists($action, $allowed_actions)) {
    $script_path = $allowed_actions[$action];
    if (file_exists($script_path)) {
        $data = $payload; 
        require_once $script_path;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno: Archivo de acción no encontrado.']);
    }
} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Error: Acción no permitida.']);
}
?>