<div class="modal fade" id="createPasswordModal" tabindex="-1" aria-labelledby="createPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPasswordModalLabel">¡Bienvenido! Unifica tu Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Para poder acceder también con tu email, por favor, crea una contraseña para tu cuenta de la biblioteca.</p>
                <form id="form-create-password">
                    <input type="hidden" name="action" value="crear_password">
                    <div class="mb-3">
                        <label for="new-password" class="form-label">Nueva Contraseña:</label>
                        <input type="password" class="form-control" name="password" id="new-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-new-password" class="form-label">Confirmar Contraseña:</label>
                        <input type="password" class="form-control" name="password_confirm" id="confirm-new-password" required>
                    </div>
                    <div id="password-feedback" class="form-feedback"></div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-link" id="btn-ignorar-unificacion">No volver a recordar</button>
                
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-password">Guardar Contraseña</button>
                </div>
            </div>
        </div>
    </div>
</div>