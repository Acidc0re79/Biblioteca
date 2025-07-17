<link rel="stylesheet" href="/assets/css/auth_form_styles.css">
<div class="form-wrapper">
  <div class="form-toggle">
    <button onclick="mostrarFormulario('login')">Iniciar Sesión</button>
    <button onclick="mostrarFormulario('registro')">Registrarse</button>
  </div>

  <div class="forms-container">

    <!-- LOGIN -->
    <form id="form-login" action="/form-handler.php" method="POST" class="form-box active">
	<input type="hidden" name="action" value="login">
      <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
  <div class="mensaje-exito">
    ✅ Registro exitoso. Esperá la activación de tu cuenta por parte del moderador.
  </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
  <div class="mensaje-error">
    <?php
      switch ($_GET['error']) {
        case 'campos': echo '⚠️ Completá todos los campos.'; break;
        case 'usuario': echo '❌ Usuario no encontrado.'; break;
        case 'clave': echo '❌ Contraseña incorrecta.'; break;
        case 'estado': echo '🚫 Cuenta inactiva o suspendida.'; break;
      }
    ?>
  </div>
<?php endif; ?>
	  <h2>Iniciar Sesión</h2>
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit" class="btn">Entrar</button>
	  <br>
		<img alt="barrita" src="assets/img/dividing-line.png" title="invert" class="invert">
      <br>
      <a href="/users/login_google.php" class="google-btn">
        <img src="assets/img/google-login.png" alt="Google" />
       </a>
	</form>

    <!-- REGISTRO -->
    <form id="form-registro" action="/form-handler.php" method="POST" class="form-box">
	<input type="hidden" name="action" value="register">
      <h2>Crear Cuenta</h2>
      <input type="text" name="nombre" placeholder="Nombre" required>
      <input type="text" name="apellido" placeholder="Apellido" required>
      <label for="fecha_nac">Fecha de nacimiento</label>
      <input type="date" name="fecha_nac" required>
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <input type="password" name="confirmar_password" placeholder="Repetir Contraseña" required>
      <input type="hidden" name="rango" value="usuario">
      <input type="hidden" name="activo" value="0">
      <button type="submit" class="btn">Registrarme</button>
	  <br>
		<img alt="barrita" src="assets/img/dividing-line.png" title="invert" class="invert">
      <br>
	  <a href="/users/login_google.php" class="google-btn">
        <img src="assets/img/google-login.png" alt="Google" />
      </a>
	</form>

  </div>
</div>

<script>
  function mostrarFormulario(tipo) {
    const container = document.querySelector('.forms-container');
    if (tipo === 'login') {
      container.style.transform = 'translateX(0%)';
    } else {
      container.style.transform = 'translateX(-50%)';
    }
  }
</script>
