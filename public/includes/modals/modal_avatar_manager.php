<div class="modal fade" id="avatarManagerModal" tabindex="-1" aria-labelledby="avatarManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarManagerModalLabel">Gestionar Mi Avatar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="avatarTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="prediseñados-tab" data-bs-toggle="tab" data-bs-target="#prediseñados" type="button" role="tab">Prediseñados</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="creaciones-tab" data-bs-toggle="tab" data-bs-target="#creaciones" type="button" role="tab">Mis Creaciones</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ia-tab" data-bs-toggle="tab" data-bs-target="#ia" type="button" role="tab">Crear con IA ✨</button>
                    </li>
                </ul>

                <div class="tab-content" id="avatarTabsContent">
                    <div class="tab-pane fade show active" id="prediseñados" role="tabpanel">
                        <div class="avatar-grid mt-3">
                            <?php
                            // Escaneamos el directorio de avatares prediseñados.
                            $predesigned_path = ROOT_PATH . '/public/assets/img/avatars/thumbs/';
                            $predesigned_files = scandir($predesigned_path);
                            
                            foreach ($predesigned_files as $file) {
                                // Ignoramos los directorios '.' y '..' y cualquier archivo que no sea una imagen.
                                if ($file !== '.' && $file !== '..' && is_file($predesigned_path . $file)) {
                                    // La ruta completa al thumbnail para mostrarlo.
                                    $thumb_url = BASE_URL . 'assets/img/avatars/thumbs/' . $file;
                                    // La ruta al archivo original para la selección.
                                    $original_path = 'assets/img/avatars/' . $file;

                                    echo '
                                    <div class="avatar-selectable" data-avatar-path="' . htmlspecialchars($original_path) . '">
                                        <img src="' . htmlspecialchars($thumb_url) . '" alt="Avatar ' . htmlspecialchars($file) . '">
                                    </div>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="creaciones" role="tabpanel">
                        <div class="avatar-grid mt-3">
                             <p class="text-center p-4">Aquí se mostrarán los avatares que hayas creado con la IA.</p>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="ia" role="tabpanel">
                        <div class="p-3">
                            <h6 class="text-center">Próximamente...</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-avatar" disabled>Guardar Avatar</button>
            </div>
        </div>
    </div>
</div>