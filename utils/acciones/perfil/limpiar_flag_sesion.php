<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
// Este script es llamado por AJAX para limpiar una bandera de sesión cuando un modal se cierra.

unset($_SESSION['password_creation_required']);

header('Content-Type: application/json');
echo json_encode(['success' => true]);