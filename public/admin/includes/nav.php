<nav class="admin-nav">
  <h3>Navegación</h3>
  <ul>
    <li><a href="/admin/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">🏠 Dashboard</a></li>
    <li><a href="/admin/usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">👥 Usuarios</a></li>
	<li><a href="/admin/insignias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'insignias.php' ? 'active' : ''; ?>">🏅 Insignias</a></li>
    <li><a href="/admin/configuracion.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'active' : ''; ?>">⚙️ Configuración</a></li>
    <li><a href="/admin/system_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'system_logs.php' ? 'active' : ''; ?>">📄 Logs del Sistema</a></li>
    <li><a href="/admin/api_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'api_logs.php' ? 'active' : ''; ?>">📡 Logs de API</a></li>
  </ul>
</nav>