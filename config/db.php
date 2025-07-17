<?php
//config/db.php (Versión 2, usando .env)

// Las credenciales ahora se leen de las variables de entorno
// cargadas por init.php
$host = getenv('DB_HOST');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$charset = getenv('DB_CHARSET');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si la conexión falla, es probable que las credenciales en .env sean incorrectas.
    // Damos un mensaje más claro.
    die("Error Crítico de Conexión a la Base de Datos: No se pudo conectar. Revisa las credenciales en tu archivo .env. Detalle del error: " . $e->getMessage());
}
