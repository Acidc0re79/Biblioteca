<?php
// Incluimos el init.php para tener acceso a la sesión ya iniciada
// y a las constantes como BASE_URL. Es la forma correcta y centralizada.
require_once dirname(__DIR__, 3) . '/config/init.php';

// Verificamos si hay un usuario en la sesión y si su rango es el adecuado.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rango'] ?? '', ['administrador', 'moderador'])) {
  
  // Si no está autorizado, lo redirigimos al formulario de login DEL FRONTEND.
  $_SESSION['error_message'] = "No tienes permisos para acceder a esta sección.";
  header('Location: ' . BASE_URL . 'index.php?p=login_form');
  exit;
}