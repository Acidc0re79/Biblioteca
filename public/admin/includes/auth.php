<?php
/**
 * Guardia de Seguridad y Punto de Inicialización para TODO el panel de administración.
 * Este es el ÚNICO archivo que debe cargar init.php.
 */

// Sube 3 niveles de directorio para encontrar la raíz del proyecto y cargar la configuración.
require_once dirname(__DIR__, 3) . '/config/init.php';

// Verificamos si hay un usuario en la sesión y si su rango es el adecuado.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rango'] ?? '', ['administrador', 'moderador'])) {
  
  // Si no está autorizado, lo redirigimos al formulario de login del FRONTEND con un mensaje.
  $_SESSION['error_message'] = "No tienes permisos para acceder a esta sección.";
  header('Location: ' . BASE_URL . 'index.php?p=login_form');
  exit;
}