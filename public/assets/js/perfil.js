document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENTOS DEL DOM ---
    const mainAvatarImg = document.getElementById('mainAvatarImg');
    const modalAvatar = document.getElementById('modalAvatar');
    const modalVerAvatar = document.getElementById('modalVerAvatar');
    const modalConfirmarEliminar = document.getElementById('modalConfirmarEliminar');
    const imagenCompletaAvatar = document.getElementById('imagenCompletaAvatar');
    let avatarIdToDelete = null;

    // Formulario de IA
    const formCrearAvatar = document.getElementById('formCrearAvatar');
    const resultadoIAContainer = document.getElementById('resultado-ia');

    // --- FUNCIONES PARA MANEJAR MODALES ---
    const openModal = (modal) => { if (modal) modal.style.display = 'flex'; };
    const closeModal = (modal) => { if (modal) modal.style.display = 'none'; };

    // --- ASIGNACIÓN DE EVENTOS ---
    document.getElementById('abrirModalAvatar')?.addEventListener('click', () => openModal(modalAvatar));
    document.getElementById('verAvatarCompleto')?.addEventListener('click', () => {
        if (mainAvatarImg && imagenCompletaAvatar) {
            imagenCompletaAvatar.src = mainAvatarImg.src;
            openModal(modalVerAvatar);
        }
    });

    document.getElementById('cerrarModalAvatar')?.addEventListener('click', () => closeModal(modalAvatar));
    document.getElementById('cerrarModalVerAvatar')?.addEventListener('click', () => closeModal(modalVerAvatar));
    document.getElementById('cancelarEliminarBtn')?.addEventListener('click', () => closeModal(modalConfirmarEliminar));

    // --- LÓGICA DE PESTAÑAS ---
    document.querySelectorAll('.modal-tabs .tab-link').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.modal-tabs .tab-link, .modal-tab-content').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
            const targetTab = document.getElementById(this.dataset.tab);
            if(targetTab) targetTab.classList.add('active');
        });
    });

    // --- DELEGACIÓN DE EVENTOS PARA ACCIONES DE AVATAR ---
    modalAvatar.addEventListener('click', function(event) {
        const target = event.target;
        if (target.classList.contains('avatar-seleccionable')) {
            guardarAvatar(target.dataset.fullImageUrl, false);
        }
        if (target.classList.contains('view-created-avatar')) {
            if (imagenCompletaAvatar) {
                imagenCompletaAvatar.src = target.dataset.fullImageUrl;
                openModal(modalVerAvatar);
            }
        }
        if (target.classList.contains('delete-created-avatar')) {
            avatarIdToDelete = target.dataset.avatarId;
            openModal(modalConfirmarEliminar);
        }
    });

    // --- FUNCIONES DE COMUNICACIÓN CON EL SERVIDOR ---
    async function guardarAvatar(avatarPath, esGenerado) {
        try {
            const response = await fetch('/public/users/actualizar_avatar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ avatar_path: avatarPath, es_generado: esGenerado })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Error al guardar: ' + result.message);
            }
        } catch (error) {
            alert('Error de conexión al guardar.');
        }
    }

    document.getElementById('confirmarEliminarBtn')?.addEventListener('click', async () => {
        if (!avatarIdToDelete) return;
        try {
            const response = await fetch('/public/acciones/eliminar_avatar_ia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ avatar_id: avatarIdToDelete })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Error al eliminar: ' + result.message);
            }
        } catch (error) {
            alert('Error de conexión al eliminar.');
        }
    });

    // ✅✅✅ CÓDIGO RESTAURADO PARA EL FORMULARIO DE IA ✅✅✅
    formCrearAvatar?.addEventListener('submit', async function(e) {
        // Prevenir la recarga de la página
        e.preventDefault(); 
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        resultadoIAContainer.innerHTML = '<p class="loading-message">Procesando tu idea...</p>';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            // Paso 1: Mejorar el prompt con Gemini
            resultadoIAContainer.innerHTML = '<p class="loading-message">Optimizando prompt con IA...</p>';
            const responseGemini = await fetch('/public/users/generar_prompt_gemini.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const resultGemini = await responseGemini.json();
            if (!resultGemini.success) throw new Error(resultGemini.message);

            // Paso 2: Generar la imagen con el prompt mejorado
            resultadoIAContainer.innerHTML = `<p class="loading-message">Generando imagen (esto puede tardar)...</p>`;
            const responseImagen = await fetch('/public/users/generar_avatar_ia.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ prompt_ia: resultGemini.prompt_mejorado })
            });
            const resultImagen = await responseImagen.json();

            if (resultImagen.success) {
                resultadoIAContainer.innerHTML = `
                    <h4>¡Aquí está tu creación!</h4>
                    <img src="${resultImagen.image_url}" alt="Avatar generado por IA" class="avatar-generado">
                    <button id="seleccionarAvatarGenerado" class="btn-perfil">Usar este avatar</button>
                `;
                // Asignar evento al nuevo botón para guardarlo
                document.getElementById('seleccionarAvatarGenerado').onclick = () => {
                    guardarAvatar(resultImagen.image_url, true);
                };
            } else {
                throw new Error(resultImagen.message);
            }
        } catch (error) {
            resultadoIAContainer.innerHTML = `<p class="error-message">Error: ${error.message}</p>`;
        } finally {
            submitButton.disabled = false;
        }
    });
});