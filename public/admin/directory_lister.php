<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estructura de Directorios</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; color: #333; }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #555; }
        textarea { width: 100%; height: 600px; font-family: monospace; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; padding: 10px; white-space: pre; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Estructura del Directorio `public`</h1>
        <p>Copia todo el contenido de este cuadro de texto y pégalo en nuestra conversación.</p>
        <textarea readonly>
<?php
/**
 * Función recursiva para listar el contenido de un directorio.
 * @param string $dir La ruta al directorio a escanear.
 * @param string $prefix El prefijo para la indentación visual.
 * @param string $base_path La ruta base para evitar que se listen ciertos archivos.
 */
function listTree($dir, $prefix = '', $base_path = '') {
    // Evita errores si el directorio no existe
    if (!is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), array('..', '.'));

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        
        // Evita que el propio script aparezca en la lista
        if ($path === $base_path . '/admin/directory_lister.php') {
            continue;
        }

        if (is_dir($path)) {
            echo $prefix . '📂 ' . $file . "\n";
            listTree($path, $prefix . '  |', $base_path);
        } else {
            echo $prefix . '📄 ' . $file . "\n";
        }
    }
}

// ✅ CAMBIO CLAVE: Empezamos a escanear desde el directorio padre
// de la ubicación actual. Si el script está en /public/admin,
// esto apuntará a /public.
$public_root_path = dirname(__DIR__);

echo "/public\n";
listTree($public_root_path, '', $public_root_path);

?>
        </textarea>
    </div>
</body>
</html>