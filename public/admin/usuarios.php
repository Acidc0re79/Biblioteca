<?php
// /admin/usuarios.php (Versión Completa y Corregida)

require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/config/init.php';

// ✅ BLOQUE DE CÓDIGO RESTAURADO
// Obtenemos todos los usuarios de la base de datos para poder mostrarlos en la tabla.
try {
    $stmt = $pdo->query("SELECT id_usuario, nombre, apellido, email, rango, estado_cuenta, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si hay un error de base de datos, detenemos la página y mostramos el error.
    die("Error crítico al obtener la lista de usuarios: " . $e->getMessage());
}
// --- FIN DEL BLOQUE RESTAURADO ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios - Admin</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
  <style>
    /* Estilos específicos para la tabla de usuarios */
    .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .user-table th, .user-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    .user-table th { background-color: #f2f2f2; font-weight: bold; }
    .user-table tr:nth-child(even) { background-color: #f9f9f9; }
    .user-table tr:hover { background-color: #f1f1f1; }
    .actions-cell a { margin-right: 10px; text-decoration: none; font-weight: bold; }
    .status-activo { color: green; }
    .status-pendiente { color: orange; }
    .status-baneado, .status-suspendido { color: red; }
    .rango-administrador { background-color: #ffcdd2; font-weight: bold; }
    .rango-moderador { background-color: #c8e6c9; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="admin-layout">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <section class="admin-content">
      <h1>Gestión de Usuarios</h1>
      <p>Desde aquí puedes ver, editar y administrar a todos los usuarios registrados en la biblioteca.</p>

      <table class="user-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>Email</th>
            <th>Rango</th>
            <th>Estado</th>
            <th>Registrado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php // Ahora la variable $usuarios existe y el foreach funcionará sin problemas ?>
          <?php foreach ($usuarios as $usuario): ?>
            <tr>
              <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
              <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
              <td><?= htmlspecialchars($usuario['email']) ?></td>
              <td class="rango-<?= htmlspecialchars($usuario['rango']) ?>">
                <?= ucfirst(htmlspecialchars($usuario['rango'])) ?>
              </td>
              <td class="status-<?= htmlspecialchars($usuario['estado_cuenta']) ?>">
                <?= ucfirst(htmlspecialchars($usuario['estado_cuenta'])) ?>
              </td>
              <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
              <td class="actions-cell">
                <a href="editar_usuario.php?id=<?= $usuario['id_usuario'] ?>">Editar</a>
                <a href="#" onclick="confirmarEliminacion(<?= $usuario['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($usuario['nombre'] . ' ' . $usuario['apellido'])) ?>')">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

  <form id="formEliminarUsuario" action="/form-handler.php" method="POST" style="display: none;">
      <input type="hidden" name="action" value="eliminar_usuario_admin">
      <input type="hidden" id="idUsuarioAEliminar" name="id_usuario">
  </form>

  <script>
    function confirmarEliminacion(id, nombre) {
        if (confirm("¿Estás seguro de que quieres eliminar a '" + nombre + "' de forma permanente? Se borrarán todos sus datos, avatares e insignias. Esta acción no se puede deshacer.")) {
            document.getElementById('idUsuarioAEliminar').value = id;
            document.getElementById('formEliminarUsuario').submit();
        }
    }
  </script>
</body>
</html>