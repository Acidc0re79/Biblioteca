// /public/assets/js/modal-handler.js (Versión con lógica para el nuevo modal)
document.addEventListener('DOMContentLoaded', function() {
    // --- SELECCIÓN DE MODALES Y BOTONES ---
    const modalAvatar = document.getElementById('modalAvatar');
    const modalVerAvatar = document.getElementById('modalVerAvatar');
    const modalConfirmarEliminar = document.getElementById('modalConfirmarEliminar');
    const modalCrearPassword = document.getElementById('modalCrearPassword'); // Nuevo modal

    const openModal = (modal) => { if (modal) modal.style.display = 'flex'; };
    const closeModal = (modal) => { if (modal) modal.style.display = 'none'; };

    // --- ABRIR MODALES ---
    document.getElementById('abrirModalAvatar')?.addEventListener('click', () => openModal(modalAvatar));
    document.getElementById('verAvatarCompleto')?.addEventListener('click', () => {
        const mainAvatarImg = document.getElementById('mainAvatarImg');
        const imagenCompletaAvatar = document.getElementById('imagenCompletaAvatar');
        if (mainAvatarImg && imagenCompletaAvatar) {
            imagenCompletaAvatar.src = mainAvatarImg.src;
            openModal(modalVerAvatar);
        }
    });

    // --- CERRAR MODALES ---
    document.getElementById('cerrarModalAvatar')?.addEventListener('click', () => closeModal(modalAvatar));
    document.getElementById('cerrarModalVerAvatar')?.addEventListener('click', () => closeModal(modalVerAvatar));
    document.getElementById('cancelarEliminarBtn')?.addEventListener('click', () => closeModal(modalConfirmarEliminar));

    // --- LÓGICA DE PESTAÑAS (sin cambios) ---
    document.querySelectorAll('.modal-tabs .tab-link').forEach(link => {
        link.addEventListener('click', function(e) { /* ... */ });
    });

    // --- ✅ LÓGICA AÑADIDA PARA EL NUEVO MODAL DE CREACIÓN DE CONTRASEÑA ---
    
    // Cierre temporal con la 'X'
    document.getElementById('cerrarModalCrearPassword')?.addEventListener('click', async () => {
        try {
            const response = await fetch('/ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'limpiar_flag_sesion' })
            });
            const result = await response.json();
            if(result.success) closeModal(modalCrearPassword);
        } catch (e) { console.error("Error al limpiar flag:", e); }
    });

    // Cierre permanente con el enlace "No volver a recordar"
    document.getElementById('ignorarUnificacionBtn')?.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            const response = await fetch('/ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'ignorar_unificacion' })
            });
            const result = await response.json();
            if(result.success) closeModal(modalCrearPassword);
        } catch (e) { console.error("Error al ignorar unificación:", e); }
    });
});