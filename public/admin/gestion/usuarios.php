<?php
// Sube un nivel para encontrar la carpeta 'includes'.
require_once __DIR__ . '/../includes/auth.php';

try {
    $stmt = $pdo->query("SELECT id_usuario, nombre, apellido, email, rango, estado_cuenta, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error crítico al obtener la lista de usuarios: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios - Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .user-table th, .user-table td { border: 1px solid #444; padding: 12px; text-align: left; }
    .user-table th { background-color: #2c2c2d; font-weight: bold; }
    .actions-cell a { margin-right: 10px; font-weight: bold; }
  </style>
</head>
<body>
  <div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/nav.php'; ?>
    <main class="admin-main">
      <?php include __DIR__ . '/../includes/header.php'; ?>
      <section class="admin-content">
        <h1>Gestión de Usuarios</h1>
        <p>Administra a todos los usuarios registrados en la biblioteca.</p>
        <table class="user-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre Completo</th>
              <th>Email</th>
              <th>Rango</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $usuario): ?>
              <tr>
                <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td><?= ucfirst(htmlspecialchars($usuario['rango'])) ?></td>
                <td><?= ucfirst(htmlspecialchars($usuario['estado_cuenta'])) ?></td>
                <td class="actions-cell">
                  <a href="editar_usuario.php?id=<?= $usuario['id_usuario'] ?>">Editar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>