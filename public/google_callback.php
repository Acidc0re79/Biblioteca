<?php
// /public/google_callback.php (Versión Final y Directa)

// 1. Inicializamos la aplicación para tener acceso a todo lo necesario.
// Usamos dirname(__DIR__) para subir desde /public a la raíz del proyecto.
require_once dirname(__DIR__) . '/config/init.php';

// 2. Una vez inicializado, llamamos directamente al script de lógica segura.
require_once ROOT_PATH . '/utils/acciones/auth/google_callback.php';
?>