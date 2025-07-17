// Se ejecuta cuando todo el contenido de la página se ha cargado.
document.addEventListener('DOMContentLoaded', function() {

    // --- MANEJO DE MODALES ---
    const createPasswordModalElement = document.getElementById('createPasswordModal');
    const viewAvatarModalElement = document.getElementById('viewAvatarModal');
    const avatarManagerModalElement = document.getElementById('avatarManagerModal'); // ✅ Nuevo

    // --- Lógica para el modal de Crear Contraseña ---
    if (createPasswordModalElement) {
        // ... (código existente sin cambios) ...
    }
    
    // --- Lógica para el modal de Ver Avatar ---
    if (viewAvatarModalElement) {
        // ... (código existente sin cambios) ...
    }
    
    // --- ✅ NUEVA LÓGICA PARA EL GESTOR DE AVATARES ---
    if (avatarManagerModalElement) {
        const btnGuardarAvatar = document.getElementById('btn-guardar-avatar');
        const avatarGrid = avatarManagerModalElement.querySelector('.avatar-grid');
        let avatarSeleccionadoPath = null; // Variable para guardar la ruta del avatar elegido

        // Usamos delegación de eventos para manejar clics en cualquier avatar
        avatarGrid.addEventListener('click', function(event) {
            // Buscamos el elemento 'avatar-selectable' más cercano al que se hizo clic
            const targetAvatar = event.target.closest('.avatar-selectable');

            if (targetAvatar) {
                // 1. Quitar la selección de cualquier otro avatar
                const allAvatars = avatarGrid.querySelectorAll('.avatar-selectable');
                allAvatars.forEach(avatar => avatar.classList.remove('selected'));

                // 2. Añadir la clase 'selected' al avatar clickeado
                targetAvatar.classList.add('selected');
                
                // 3. Guardar la ruta del avatar y activar el botón
                avatarSeleccionadoPath = targetAvatar.dataset.avatarPath;
                btnGuardarAvatar.disabled = false;
            }
        });

        // Lógica para el botón "Guardar Avatar"
        btnGuardarAvatar.addEventListener('click', function() {
            if (!avatarSeleccionadoPath) return; // No hacer nada si no hay selección

            // Creamos los datos para enviar vía AJAX
            const formData = new FormData();
            formData.append('action', 'actualizar_avatar');
            formData.append('avatar_path', avatarSeleccionadoPath);

            // Enviamos la petición
            fetch(BASE_URL + 'ajax-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Si todo va bien, recargamos la página para ver el cambio
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error en la petición AJAX:', error);
                alert('Ocurrió un error de conexión.');
            });
        });
    }
});