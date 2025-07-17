<?php
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['administrador', 'moderador'])) {
  header("Location: /admin/login.php");
  exit;
}
