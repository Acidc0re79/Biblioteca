<?php
// Variables para el mensaje de error
$titulo_error = "404 - Página Perdida";
$mensaje_principal = "Parece que la página que buscas se ha perdido en el multiverso digital."; // Mensaje por defecto
$mostrar_botones = true;

// Verificamos si la redirección fue por un error de seguridad.
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'seguridad':
        case 'acceso':
            $titulo_error = "🚫 Acceso Denegado";
            $mensaje_principal = "No tienes los permisos necesarios para acceder a esta sección. Si crees que es un error, contacta a un administrador.";
            break;
    }
} else {
    // Si no es un error de seguridad, intentamos cargar una frase poética.
    // ¡NUEVA RUTA! Ahora apunta a la carpeta /assets/text/
    $ruta_frases = ROOT_PATH . '/public/assets/text/frases_404.txt';

    if (file_exists($ruta_frases)) {
        // file() lee el archivo en un array, cada línea es un elemento.
        $frases_poeticas = file($ruta_frases, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Si el archivo tiene frases, elegimos una al azar.
        if (!empty($frases_poeticas)) {
            $mensaje_principal = $frases_poeticas[array_rand($frases_poeticas)];
        }
    }
}
?>

<div class="error-404">
    <img src="/assets/img/404-libro-perdido.png" alt="Página no encontrada" class="error-img">
    <h1><?= htmlspecialchars($titulo_error) ?></h1>
    <p><?= htmlspecialchars($mensaje_principal) ?></p>

    <?php if ($mostrar_botones): ?>
        <div class="error-buttons">
            <a href="/index.php" class="btn-error">🏠 Ir al Inicio</a>
            <a href="/index.php?pagina=perfil" class="btn-error">👤 Ir al Perfil</a>
            <a href="/index.php?pagina=catalogo" class="btn-error">📚 Ver Catálogo</a>
        </div>
    <?php endif; ?>
</div>