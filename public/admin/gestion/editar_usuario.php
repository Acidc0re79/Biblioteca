<?php
// Sube un nivel para encontrar la carpeta 'includes'.
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}
$id_usuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los datos del usuario: " . $e->getMessage());
}
if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}
$roles_permitidos = ['lector', 'moderador', 'administrador'];
$estados_permitidos = ['activo', 'pendiente', 'suspendido', 'baneado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario - Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Estilos específicos para el formulario de edición */
  </style>
</head>
<body>
  <div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/nav.php'; ?>
    <main class="admin-main">
      <?php include __DIR__ . '/../includes/header.php'; ?>
      <section class="admin-content">
        <h1>Editar Usuario: <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h1>
        <form action="<?= BASE_URL ?>form-handler.php" method="POST">
          <input type="hidden" name="action" value="actualizar_usuario_admin">
          <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
          
          <button type="submit">Guardar Cambios</button>
        </form>
      </section>
    </main>
  </div>
</body>
</html>