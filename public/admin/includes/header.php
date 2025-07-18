<header class="admin-header">
  <div class="header-title">
    <h2>📚 Biblioteca SYS — Admin</h2>
  </div>
  <div class="admin-user">
    <span>Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?></span>
    <a href="/" target="_blank">Ver Sitio</a>
    
    <form action="<?= BASE_URL ?>form-handler.php" method="POST" style="display: inline;">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="logout-btn">Cerrar Sesión</button>
    </form>
  </div>
</header>

<style>
  .admin-header { display: flex; justify-content: space-between; align-items: center; }
  .admin-user { display: flex; align-items: center; gap: 1.5rem; }
  .logout-btn { background: none; border: 1px solid #dc3545; color: #dc3545; padding: 5px 10px; border-radius: 5px; cursor: pointer; transition: all 0.2s ease; }
  .logout-btn:hover { background: #dc3545; color: white; }
</style>