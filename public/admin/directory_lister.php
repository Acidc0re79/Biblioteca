<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estructura de Directorios</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; color: #333; }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #555; }
        textarea { width: 100%; height: 500px; font-family: monospace; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; padding: 10px; }
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
 *
 * @param string $dir La ruta al directorio a escanear.
 * @param string $prefix El prefijo para la indentación visual.
 */
function listDirectory($dir, $prefix = '') {
    // Escanea el directorio y filtra '.' y '..'
    $files = array_diff(scandir($dir), array('..', '.'));

    foreach ($files as $file) {
        // Ignora este mismo script para no listarlo.
        if ($file === 'directory_lister.php') {
            continue;
        }

        // Imprime el nombre del archivo o carpeta.
        echo $prefix . '📂 ' . $file . "\n";

        // Si es un directorio, llama a la función de nuevo para ese subdirectorio.
        if (is_dir($dir . '/' . $file)) {
            listDirectory($dir . '/' . $file, $prefix . '  |');
        }
    }
}

// Inicia el proceso desde el directorio actual (donde se encuentra este script).
echo "/public\n";
listDirectory(__DIR__);

?>
        </textarea>
    </div>
</body>
</html>