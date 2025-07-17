<?php
// /utils/debug_helper.php

// Esta función se usará para registrar mensajes de depuración condicionalmente.
// Si DEBUG_MODE es true, el mensaje se escribirá en el log de errores del servidor.
// Opcionalmente, si $to_browser es true y no se han enviado cabeceras, intentará imprimir en el navegador (¡usar con precaución en APIs!).
function syslr_debug_log($message, $to_browser = false) {
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        // Siempre escribir en el log de errores del servidor
        error_log("[DEBUG] " . $message);

        // Imprimir en el navegador solo si se solicita y no se han enviado cabeceras (para evitar errores)
        if ($to_browser && !headers_sent()) {
            echo "<pre>[DEBUG BROWSER] " . htmlspecialchars($message) . "</pre>";
        }
    }
}

// Opcional: Función para iniciar un buffer de salida si necesitas imprimir mucho debug en el navegador desde un script que normalmente envía JSON
// function syslr_start_debug_buffer() {
//     if (defined('DEBUG_MODE') && DEBUG_MODE === true && !headers_sent()) {
//         ob_start();
//     }
// }

// Opcional: Función para finalizar y enviar/limpiar el buffer de depuración
// function syslr_end_debug_buffer($send_to_browser = false) {
//     if (defined('DEBUG_MODE') && DEBUG_MODE === true && ob_get_level() > 0) {
//         if ($send_to_browser) {
//             ob_end_flush(); // Enviar el contenido del buffer al navegador
//         } else {
//             ob_end_clean(); // Limpiar el buffer sin enviarlo
//         }
//     }
// }