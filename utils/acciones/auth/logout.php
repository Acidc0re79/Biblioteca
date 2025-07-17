<?php
session_start();

// Limpiar sesión
$_SESSION = [];
session_unset();
session_destroy();

// Redirigir al inicio con mensaje
header("Location: /index.php?logout=ok");
exit;
