// /public/assets/js/debug-logger.js

/**
 * Envía un evento de log al servidor para ser guardado.
 * Esta función está diseñada para fallar silenciosamente y no molestar al usuario,
 * solo registrará el error en la consola del desarrollador si no puede contactar al servidor.
 * @param {string} type - El tipo de error o evento (ej. 'save_avatar_failed').
 * @param {object} details - Un objeto con la información detallada del error.
 */
async function logFrontendEvent(type, details) {
    try {
        // No esperamos una respuesta, solo enviamos los datos.
        await fetch('/utils/log_frontend_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: type,
                details: details
            })
        });
    } catch (error) {
        // Si el envío del log falla, lo mostramos solo en la consola para no interrumpir al usuario.
        console.error('Error al intentar enviar el log de frontend al servidor:', error);
    }
}