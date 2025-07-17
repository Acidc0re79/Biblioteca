<?php
// /public/google_callback.php (Versión Final Segura)

// 1. Cargamos el entorno de la aplicación.
// Esta línea es todo lo que necesita para tener acceso a la base de datos,
// constantes, y configuraciones.
require_once __DIR__ . '/../config/init.php';

// 2. Una vez inicializado, llamamos al script de lógica segura que contiene
// todas las dinámicas y parámetros específicos para manejar la respuesta de Google.
require_once ROOT_PATH . '/utils/acciones/auth/google_callback.php';