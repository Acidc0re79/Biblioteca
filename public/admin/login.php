<?php
session_start();

// Si ya hay sesión iniciada y es válida, redirigir al panel
if (isset($_SESSION['usuario_id']) && in_array($_SESSION['rango'] ?? '', ['administrador', 'moderador'])) {
  header("Location: /admin/index.php");
  exit;
} elseif (isset($_SESSION['usuario_id'])) {
  die("⚠️ Estás logueado pero no tenés permisos para acceder al panel.");
}

require_once '/srv/disk7/4540860/www/biblioteca.syslr.com.ar/config/db.php';
$pepper = trim(file_get_contents('/srv/disk7/4540860/www/biblioteca.syslr.com.ar/config/pepper.key'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && !empty($user['hash_password']) && !empty($user['salt'])) {
    $peppered = hash_hmac("sha256", $password, $pepper);
    $pass_verificar = $peppered . $user['salt'];

    if (password_verify($pass_verificar, $user['hash_password'])) {
        if ($user['estado_cuenta'] !== 'activo') {
            $error = "🚫 Cuenta no activa: " . htmlspecialchars($user['estado_cuenta']);
        } elseif (!in_array($user['rango'], ['administrador', 'moderador'])) {
            $error = "⚠️ Sin permisos para acceder al panel.";
        } else {
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rango'];
            header("Location: /admin/index.php");
            exit;
        }
    } else {
        $error = "❌ Contraseña incorrecta.";
    }
} else {
    $error = "❌ Usuario no encontrado o mal formado.";
}
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Administrador</title>
  <link rel="stylesheet" href="/admin/assets/css/login.css">
</head>
<body>
  <div class="login-bg">
    <div class="login-box">
      <h2>Ingreso al Panel</h2>
      <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
      <form method="post">
        <div class="input-group">
          <input type="email" name="email" placeholder="Correo electrónico" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit">Ingresar</button>
      </form>
    </div>
  </div>
</body>
</html>
