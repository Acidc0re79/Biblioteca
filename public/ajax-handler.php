<?php
// 1. Carga la configuración y el entorno de la aplicación.
require_once __DIR__ . '/../config/init.php';

// 2. Establece el tipo de contenido de la respuesta a JSON.
// Todas las respuestas AJAX de este manejador serán en formato JSON.
header('Content-Type: application/json');

// 3. Obtiene la acción solicitada.
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// 4. Define una lista blanca de acciones AJAX permitidas.
$allowed_actions = [
    // Acciones de Perfil
    'actualizar_perfil'   => ROOT_PATH . '/utils/acciones/perfil/actualizar_perfil.php',
    'crear_password'      => ROOT_PATH . '/utils/acciones/perfil/crear_password.php',
    'ignorar_unificacion' => ROOT_PATH . '/utils/acciones/perfil/ignorar_unificacion.php',
    
    // Acciones de Avatares
    'actualizar_avatar'   => ROOT_PATH . '/utils/acciones/perfil/actualizar_avatar.php',
    'eliminar_avatar'     => ROOT_PATH . '/utils/acciones/perfil/eliminar_avatar_ia.php',
    'generar_avatar_ia'   => ROOT_PATH . '/utils/acciones/ia/generar_avatar_ia.php',
];

// 5. Verifica y ejecuta la acción.
if (array_key_exists($action, $allowed_actions)) {
    // La seguridad principal (verificar si el usuario está logueado, etc.)
    // debe estar DENTRO de cada script de acción.
    require_once $allowed_actions[$action];
} else {
    // Si la acción no es válida, registra el error y devuelve una respuesta JSON.
    log_system_event("ajax-handler.php: Se recibió una acción no válida.", ['accion_recibida' => $action]);
    
    // Devolvemos una respuesta de error en formato JSON estandarizado.
    echo json_encode([
        'status' => 'error',
        'message' => 'La acción solicitada no es válida.'
    ]);
    exit;
}