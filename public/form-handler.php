<?php
// /public/form-handler.php (Versión Final y Simplificada)

// 1. Definimos la ruta raíz ANTES de cualquier otra cosa.
// Esto es crucial para que los scripts que llamemos sepan dónde encontrar los archivos de configuración.
define('ROOT_PATH', dirname(__DIR__));

$action = $_POST['action'] ?? null;

if (empty($action)) {
    die('Error: Acción de formulario no especificada.');
}

// 2. La lista de acciones permitidas.
$allowed_actions = [
    'login'    => ROOT_PATH . '/utils/acciones/auth/procesar_login.php',
    'register' => ROOT_PATH . '/utils/acciones/auth/procesar_registro.php',
    'logout'   => ROOT_PATH . '/utils/acciones/auth/logout.php',
	'crear_password'  => ROOT_PATH . '/utils/acciones/perfil/crear_password.php',
	'actualizar_usuario_admin' => ROOT_PATH . '/utils/acciones/admin/actualizar_usuario.php',
	'eliminar_usuario_admin'   => ROOT_PATH . '/utils/acciones/admin/eliminar_usuario.php'
];

if (array_key_exists($action, $allowed_actions)) {
    // 3. Simplemente llamamos al script y dejamos que él se encargue de todo lo demás.
    require_once $allowed_actions[$action];
} else {
    die('Error: Acción de formulario no permitida.');
}
?>