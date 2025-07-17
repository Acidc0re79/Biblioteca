<header class="header">
  <div class="logo">
    <img src="/assets/img/logo.png" alt="Logo Biblioteca" />
    <h1>Biblioteca Digital SYS</h1>
  </div>

  <div class="login-actions">
    <?php if (!isset($_SESSION['usuario_id'])): ?>
      
      <a href="/index.php?pagina=login_form" class="btn-header">Iniciar Sesión</a>
      <a href="/index.php?pagina=login_form" class="btn-header btn-secondary">Registrarse</a>
      <a href="/users/google_login.php" class="btn-header btn-google">
        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google G Logo">
        Entrar con Google
      </a>

    <?php else: ?>
      
      <?php
        // --- LÓGICA DE AVATAR CORREGIDA Y OPTIMIZADA PARA EL HEADER ---
        $url_avatar_header = '/assets/img/default_avatar.png'; // Empezamos con el default

        // 1. Revisamos si hay un avatar seleccionado
        if (!empty($_SESSION['avatar_seleccionado'])) {
            // Verificamos si es uno prediseñado para usar la miniatura
            if (str_starts_with($_SESSION['avatar_seleccionado'], '/assets/img/avatars/')) {
                $url_avatar_header = str_replace(
                    '/assets/img/avatars/', 
                    '/assets/img/avatars/thumbs/', 
                    $_SESSION['avatar_seleccionado']
                );
            } else { // Es generado por el usuario (no tiene thumb)
                $url_avatar_header = '/assets/img/avatars/users/' . $_SESSION['avatar_seleccionado'];
            }
        // 2. Si no, revisamos si tiene un avatar de Google
        } elseif (!empty($_SESSION['avatar_google'])) {
            $url_avatar_header = $_SESSION['avatar_google'];
        }
      ?>

      <div class="user-menu">
        <img src="<?= htmlspecialchars($url_avatar_header) ?>" 
             alt="Avatar" class="user-avatar" id="avatarBtn">

        <div class="user-dropdown" id="userDropdown">
          <button class="dropdown-close" id="closeDropdownBtn">&times;</button>
        
          <p><strong><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></strong></p>
          <p class="estado">Estado: 
            <span class="<?= htmlspecialchars($_SESSION['estado_cuenta'] ?? 'pendiente') ?>">
              <?= ucfirst(htmlspecialchars($_SESSION['estado_cuenta'] ?? 'pendiente')) ?>
            </span>
          </p>
          <p class="rango">Nivel: <?= htmlspecialchars($_SESSION['rango'] ?? 'Lector') ?></p>
          <p>Puntos: <?= htmlspecialchars($_SESSION['puntos'] ?? 0) ?></p>
          
          <a href="/index.php?pagina=perfil" class="dropdown-link">🧾 Mi Perfil</a>
          
          <?php if (in_array($_SESSION['rango'] ?? '', ['administrador', 'moderador'])): ?>
            <a href="/admin/index.php" class="dropdown-link admin-panel-link">⚙️ Panel Admin</a>
          <?php endif; ?>
          
          <form action="/form-handler.php" method="POST" style="margin-top: 10px;">
    <input type="hidden" name="action" value="logout">
    <button type="submit" class="dropdown-link cerrar-sesion">🔒 Cerrar sesión</button>
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