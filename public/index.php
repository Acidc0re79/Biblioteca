<?php
// /public/index.php (Versión Corregida y Robusta)

// 1. Define la ruta raíz del proyecto de forma explícita y segura.
// dirname(__DIR__) toma la ruta del directorio actual (/public) y sube un nivel,
// apuntando directamente a la raíz de tu proyecto.
$project_root = dirname(__DIR__);

// 2. Ahora, incluye el archivo de inicialización usando esa ruta absoluta y sin ambigüedades.
require_once $project_root . '/config/init.php';

// A partir de aquí, el resto del archivo HTML no necesita ningún cambio.
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca Digital SYS</title>
  <link rel="stylesheet" href="/assets/css/estructura.css">
  <link rel="stylesheet" href="/assets/css/header.css">
  
  <?php
    // --- LÓGICA PARA CARGAR EL TEMA DINÁMICO ---
    $tema_actual = $_SESSION['tema'] ?? 'default';
    $ruta_tema_css = "/themes/{$tema_actual}/theme.css";
    $ruta_fisica_tema_css = ROOT_PATH . '/public' . $ruta_tema_css;
    
    if (!file_exists($ruta_fisica_tema_css)) {
        $ruta_tema_css = "/themes/default/theme.css";
    }
  ?>
  
  <link rel="stylesheet" href="<?= $ruta_tema_css ?>">
  
</head>
<body>
  <?php include ROOT_PATH . '/public/includes/header.php'; ?>

  <?php
  // ✅ INICIO DE LA MODIFICACIÓN
  // Ahora, solo bloqueamos al usuario si NO tiene una sesión de login completa
  // Y TAMPOCO tiene la sesión temporal para crear una contraseña.
  if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['password_creation_required'])) {
    // Si no tiene ninguna de las dos, mostramos el formulario de login.
    echo "<p style='color: red; font-weight: bold;'>Debes iniciar sesión para acceder al contenido.</p>";
    include ROOT_PATH . '/public/users/login_form.php';
  } else {
    // Si tiene una sesión (completa o temporal), le mostramos el contenido normal.
    include ROOT_PATH . '/public/includes/nav.php'; ?>

    <main class="layout">
      <?php include ROOT_PATH . '/public/includes/sidebar.php'; ?>

      <section class="content">
        <?php
        // Carga segura y dinámica de páginas desde /paginas/
        $carpeta_paginas = ROOT_PATH . '/public/paginas/';
        $pagina_predeterminada = 'insignias.php';

        $archivos_permitidos = array_filter(scandir($carpeta_paginas), function($archivo) {
          return pathinfo($archivo, PATHINFO_EXTENSION) === 'php';
        });

        $paginas_validas = array_map(function($archivo) {
          return basename($archivo, '.php');
        }, $archivos_permitidos);

        $pagina = isset($_GET['pagina']) ? basename($_GET['pagina']) : basename($pagina_predeterminada, '.php');

        if (in_array($pagina, $paginas_validas) && file_exists($carpeta_paginas . $pagina . '.php')) {
          include $carpeta_paginas . $pagina . '.php';
        } else {
          include $carpeta_paginas . '404.php';
        }
        ?>
      </section>
    </main>
  <?php } ?>
  <?php // ✅ FIN DE LA MODIFICACIÓN ?>
    <?php include ROOT_PATH . '/public/includes/footer.php'; ?>
</body>
</html>