<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/auth_form_styles.css?v=<?php echo time(); ?>">

<div class="form-wrapper">
  <div class="form-toggle">
    <button id="btn-login" class="active">Iniciar Sesión</button>
    <button id="btn-register">Registrarse</button>
  </div>

  <div class="forms-container">
    <form id="form-login" action="<?php echo BASE_URL; ?>form-handler.php" method="POST" class="form-box">
      <input type="hidden" name="action" value="login">
      <h2>Iniciar Sesión</h2>
      <input type="text" name="username" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit" class="btn">Entrar</button>
      <div class="form-separator">o</div>
      <a href="<?php echo BASE_URL; ?>public/google_login.php" class="google-btn">
        <img src="<?php echo BASE_URL; ?>assets/img/google_icon.png" alt="Google">
        <span>Continuar con Google</span>
      </a>
    </form>

    <form id="form-registro" action="<?php echo BASE_URL; ?>form-handler.php" method="POST" class="form-box">
      <input type="hidden" name="action" value="register">
      <h2>Crear Cuenta</h2>
      <input type="text" name="nombre" placeholder="Nombre" required>
      <input type="text" name="apellido" placeholder="Apellido" required>
      
      <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
      <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required class="form-input">
      
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña (mín. 8 caracteres)" required>
      <input type="password" name="password_confirm" placeholder="Repetir Contraseña" required>
      <button type="submit" class="btn">Registrarme</button>
    </form>
  </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/auth-form.js?v=<?php echo time(); ?>"></script>