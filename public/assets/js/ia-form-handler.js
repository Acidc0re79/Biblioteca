// /public/assets/js/ia-form-handler.js
document.addEventListener('DOMContentLoaded', function() {
    const formCrearAvatar = document.getElementById('formCrearAvatar');
    const resultadoIAContainer = document.getElementById('resultado-ia');

    formCrearAvatar?.addEventListener('submit', async function(e) {
        e.preventDefault(); 
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        resultadoIAContainer.innerHTML = '<p class="loading-message">Iniciando proceso creativo...</p>';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            // Paso 1: Mejorar el prompt con Gemini
            resultadoIAContainer.innerHTML = '<p class="loading-message">Optimizando prompt con IA...</p>';
            const responseGemini = await fetch('/ajax-handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const resultGemini = await responseGemini.json();
            if (!resultGemini.success) throw new Error('Fallo en Gemini: ' + resultGemini.message);

            // Paso 2: Generar la imagen con el prompt mejorado
            resultadoIAContainer.innerHTML = `<p class="loading-message">Generando imagen...</p>`;
            const responseImagen = await fetch('/ajax-handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ prompt_ia: resultGemini.prompt_mejorado })
            });
            const resultImagen = await responseImagen.json();

            if (resultImagen.success) {
                resultadoIAContainer.innerHTML = `
                    <h4>¡Aquí está tu creación!</h4>
                    <img src="${resultImagen.image_url}" alt="Avatar generado" class="avatar-generado">
                    <button id="seleccionarAvatarGenerado" class="btn-perfil">Usar este avatar</button>
                `;
                // Asignar evento al nuevo botón.
                // Como la función guardarAvatar no está en este archivo, la forma más simple es recargar la página.
                document.getElementById('seleccionarAvatarGenerado').onclick = () => {
                   location.reload();
                };
            } else {
                throw new Error('Fallo en Generador de Imagen: ' + resultImagen.message);
            }
        } catch (error) {
            // Si algo falla en cualquiera de los pasos, lo reportamos.
            await logFrontendEvent('ia_generation_failed', { 
                error: error.message, 
                formData: data 
            });
            resultadoIAContainer.innerHTML = `<p class="error-message">Error: ${error.message}</p>`;
        } finally {
            submitButton.disabled = false;
        }
    });
});