// /public/assets/js/avatar-manager.js
document.addEventListener('DOMContentLoaded', function() {
    // Selección de elementos del DOM
    const modalAvatar = document.getElementById('modalAvatar');
    const modalVerAvatar = document.getElementById('modalVerAvatar');
    const modalConfirmarEliminar = document.getElementById('modalConfirmarEliminar');
    const imagenCompletaAvatar = document.getElementById('imagenCompletaAvatar');
    let avatarIdToDelete = null;

    // Funciones auxiliares para abrir y cerrar modales
    const openModal = (modal) => { if (modal) modal.style.display = 'flex'; };
    const closeModal = (modal) => { if (modal) modal.style.display = 'none'; };

    // Función para guardar el avatar seleccionado, ahora con logging
    async function guardarAvatar(avatarPath, esGenerado = false) {
        try {
            const response = await fetch('/ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ avatar_path: avatarPath, es_generado: esGenerado })
            });
            const result = await response.json();

            if (result.success) {
                location.reload();
            } else {
                // Si el servidor responde con un error, lo logueamos antes de alertar al usuario.
                await logFrontendEvent('save_avatar_failed', { error: result.message, path: avatarPath });
                alert('Error al guardar: ' + result.message);
            }
        } catch (error) {
            // Si hay un error de conexión (el fetch falla), también lo logueamos.
            await logFrontendEvent('save_avatar_exception', { error: error.message, path: avatarPath });
            console.error("Error de conexión o JSON en guardarAvatar:", error);
            alert('Error de conexión. Revisa la consola (F12) para más detalles.');
        }
    }

    // Función para confirmar la eliminación, ahora con logging
    document.getElementById('confirmarEliminarBtn')?.addEventListener('click', async () => {
        if (!avatarIdToDelete) return;
        try {
            const response = await fetch('/ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ avatar_id: avatarIdToDelete })
            });
            const result = await response.json();

            if (result.success) {
                location.reload();
            } else {
                await logFrontendEvent('delete_avatar_failed', { error: result.message, avatar_id: avatarIdToDelete });
                alert('Error al eliminar: ' + result.message);
            }
        } catch (error) {
            await logFrontendEvent('delete_avatar_exception', { error: error.message, avatar_id: avatarIdToDelete });
            alert('Error de conexión al eliminar.');
        }
    });

    // Delegación de eventos para todas las acciones dentro del modal principal
    modalAvatar?.addEventListener('click', function(event) {
        const target = event.target;

        // Acción: Seleccionar un avatar
        if (target.classList.contains('avatar-seleccionable')) {
            guardarAvatar(target.dataset.fullImageUrl, false);
        }
        
        // Acción: Ver un avatar en grande
        if (target.classList.contains('view-created-avatar')) {
            if (imagenCompletaAvatar) {
                imagenCompletaAvatar.src = target.dataset.fullImageUrl;
                openModal(modalVerAvatar);
            }
        }

        // Acción: Abrir confirmación para eliminar
        if (target.classList.contains('delete-created-avatar')) {
            avatarIdToDelete = target.dataset.avatarId;
            openModal(modalConfirmarEliminar);
        }
    });
});