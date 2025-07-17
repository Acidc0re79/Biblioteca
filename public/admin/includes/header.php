<header class="admin-header">
  <h2>📚 Biblioteca SYS — Admin</h2>
  <div class="admin-user">
    <span><?= $_SESSION['usuario_nombre'] ?? 'Administrador' ?></span>
	<a href="/index.php">Ir a la web</a>
    <a href="/admin/logout.php">Cerrar sesión</a>
  </div>
</header>
