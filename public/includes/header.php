<header class="header">
  <div class="logo">
    <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo Biblioteca" />
    <h1>Biblioteca Digital SYS</h1>
  </div>

  <div class="login-actions">
    <?php if (!isset($_SESSION['user_id'])): ?>
      
      <a href="<?php echo BASE_URL; ?>index.php?p=login_form" class="btn-header">Iniciar Sesión</a>
      <a href="<?php echo BASE_URL; ?>index.php?p=login_form" class="btn-header btn-secondary">Registrarse</a>
      <a href="<?php echo BASE_URL; ?>users/google_login.php" class="btn-header btn-google">
        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google G Logo">
        Entrar con Google
      </a>

    <?php else: ?>
      
      <?php
        // Lógica de avatar simplificada usando la función helper
        $url_avatar_header = get_avatar_url($_SESSION['avatar_actual'] ?? null, $_SESSION['email']);
      ?>

      <div class="user-menu">
        <img src="<?= htmlspecialchars($url_avatar_header) ?>" alt="Avatar" class="user-avatar" id="avatarBtn">

        <div class="user-dropdown" id="userDropdown">
          <button class="dropdown-close" id="closeDropdownBtn">&times;</button>
        
          <p><strong><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></strong></p>
          <p class="estado">Estado: 
            <span class="<?= htmlspecialchars($_SESSION['estado_cuenta'] ?? 'pendiente') ?>">
              <?= ucfirst(htmlspecialchars($_SESSION['estado_cuenta'] ?? 'pendiente')) ?>
            </span>
          </p>
          <p class="rango">Nivel: <?= htmlspecialchars($_SESSION['rol'] ?? 'usuario') ?></p>
          
          <a href="<?php echo BASE_URL; ?>index.php?p=perfil" class="dropdown-link"> Mi Perfil</a>
          
          <?php if (in_array($_SESSION['rol'] ?? '', ['administrador', 'moderador'])): ?>
            <a href="<?php echo BASE_URL; ?>admin/index.php" class="dropdown-link admin-panel-link"> Panel Admin</a>
          <?php endif; ?>
          
          <form action="<?php echo BASE_URL; ?>form-handler.php" method="POST" style="margin-top: 10px;">
              <input type="hidden" name="action" value="logout">
              <button type="submit" class="dropdown-link cerrar-sesion"> Cerrar sesión</button>
          </form>
        </div>
      </div>

    <?php endif; ?>
  </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const avatarBtn = document.getElementById('avatarBtn');
  const userDropdown = document.getElementById('userDropdown');
  const closeDropdownBtn = document.getElementById('closeDropdownBtn');

  if (avatarBtn && userDropdown && closeDropdownBtn) {
    avatarBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle('active');
    });
    closeDropdownBtn.addEventListener('click', () => {
      userDropdown.classList.remove('active');
    });
    document.addEventListener('click', (e) => {
      if (userDropdown.classList.contains('active') && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('active');
      }
    });
  }
});
</script>