<?php
session_start();
session_unset();
session_destroy();

// Redirigir a la página principal o login
header("Location: /index.php");
exit;
