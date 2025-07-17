<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/config/init.php';

// Verificamos que se haya pasado un ID de usuario
if (!isset($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id_usuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Obtenemos los datos del usuario específico
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los datos del usuario: " . $e->getMessage());
}

// Si no se encuentra el usuario, redirigimos a la lista
if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

// Listas de opciones para los selectores
$roles_permitidos = ['lector', 'moderador', 'administrador'];
$estados_permitidos = ['activo', 'pendiente', 'suspendido', 'baneado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario - Admin</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
  <style>
    .edit-user-form { max-width: 700px; }
    .form-card { background: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
    .form-card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    .form-actions { text-align: right; }
    .btn-primary { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    .btn-danger { background-color: #dc3545; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="admin-layout">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <section class="admin-content">
      <h1>Editar Usuario: <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h1>

      <form class="edit-user-form" action="/form-handler.php" method="POST">
        <input type="hidden" name="action" value="actualizar_usuario_admin">
        <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

        <div class="form-card">
          <h3>Información Principal</h3>
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
          </div>
          <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email (no editable)</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" disabled>
          </div>
        </div>

        <div class="form-card">
          <h3>Permisos y Estado</h3>
          <div class="form-group">
            <label for="rango">Rango</label>
            <select id="rango" name="rango">
              <?php foreach ($roles_permitidos as $rol): ?>
                <option value="<?= $rol ?>" <?= ($usuario['rango'] == $rol) ? 'selected' : '' ?>><?= ucfirst($rol) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="estado_cuenta">Estado de la Cuenta</label>
            <select id="estado_cuenta" name="estado_cuenta">
              <?php foreach ($estados_permitidos as $estado): ?>
                <option value="<?= $estado ?>" <?= ($usuario['estado_cuenta'] == $estado) ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-card">
          <h3>Gamificación</h3>
          <div class="form-group">
            <label for="puntos">Puntos</label>
            <input type="number" id="puntos" name="puntos" value="<?= htmlspecialchars($usuario['puntos']) ?>">
          </div>
          <div class="form-group">
            <label>Intentos de Avatar Usados</label>
            <input type="number" value="<?= htmlspecialchars($usuario['intentos_avatar']) ?>" disabled>
            <input type="checkbox" id="resetear_intentos" name="resetear_intentos" value="1">
            <label for="resetear_intentos">Resetear intentos a 0</label>
          </div>
        </div>

        <div class="form-card">
          <h3>Seguridad</h3>
          <div class="form-group">
            <label for="password">Nueva Contraseña (dejar en blanco para no cambiar)</label>
            <input type="password" id="password" name="password" autocomplete="new-password">
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>