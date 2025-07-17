<?php
// Asegurarnos de que el usuario está logueado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['password_creation_required'])) {
    // Si no hay sesión completa NI sesión temporal, entonces sí lo sacamos.
    header("Location: /index.php?pagina=login_form&error=acceso");
    exit;
}

// Determinamos el ID de usuario, ya sea de una sesión completa o temporal
$id_usuario = $_SESSION['usuario_id'] ?? $_SESSION['temp_usuario_id'];

// 1. Obtener toda la información del usuario
$stmt_usuario = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1");
$stmt_usuario->execute(['id' => $id_usuario]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<p>Error: No se pudo cargar la información del perfil.</p>";
    return;
}

// 2. Lógica para determinar el avatar final a mostrar
$url_avatar_final = '/assets/img/default_avatar.png';
if (!empty($usuario['avatar_seleccionado'])) {
    if (str_starts_with($usuario['avatar_seleccionado'], '/assets/')) {
        $url_avatar_final = $usuario['avatar_seleccionado'];
    } else {
        $url_avatar_final = '/assets/img/avatars/users/' . htmlspecialchars($usuario['avatar_seleccionado']);
    }
} elseif (!empty($usuario['avatar_google'])) {
    $url_avatar_final = htmlspecialchars($usuario['avatar_google']);
}

// 3. Obtener avatares y opciones para los modales
$avatares_prediseñados_thumbs = glob(ROOT_PATH . '/public/assets/img/avatars/thumbs/*.{jpg,png,gif}', GLOB_BRACE);
if ($avatares_prediseñados_thumbs === false) { $avatares_prediseñados_thumbs = []; }

$stmt_creaciones = $pdo->prepare("SELECT id, nombre_archivo FROM usuarios_avatares WHERE id_usuario = ? ORDER BY fecha_creacion DESC");
$stmt_creaciones->execute([$id_usuario]);
$mis_creaciones_full = $stmt_creaciones->fetchAll(PDO::FETCH_ASSOC);

function cargarOpcionesIA($filepath) {
    if (file_exists($filepath)) {
        return json_decode(file_get_contents($filepath), true) ?: [];
    }
    return [];
}
$opciones_ia = [
    'estilo_ia' => ['label' => 'Estilo Visual', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_styles.json')],
    'tipo_sujeto_ia' => ['label' => 'Tipo de Sujeto', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_subject_types.json')],
    'accion_ia' => ['label' => 'Acción o Pose', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_actions.json')],
    'entorno_ia' => ['label' => 'Entorno / Fondo', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_environments.json')],
    'iluminacion_ia' => ['label' => 'Iluminación', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_lighting.json')],
    'paleta_color_ia' => ['label' => 'Paleta de Colores', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_color_palettes.json')],
    'composicion_angulo_ia' => ['label' => 'Composición / Ángulo', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_composition_angles.json')],
    'rendering_details_ia' => ['label' => 'Render / Nivel de Detalle', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_rendering_details.json')],
    'emotional_tone_ia' => ['label' => 'Tono Emocional', 'options' => cargarOpcionesIA(ROOT_PATH . '/public/assets/text/ia_emotional_tones.json')]
];

$cantidad_creados = count($mis_creaciones_full);
$total_permitidos = CONFIG_SITIO['intentos_avatar_iniciales'] ?? 50;
$intentos_utilizados_usuario = $usuario['intentos_avatar'] ?? 0;
$intentos_disponibles = max(0, $total_permitidos - $intentos_utilizados_usuario);

// Lógica para el disparador del modal de creación de contraseña
$show_password_modal = isset($_SESSION['password_creation_required']) && $_SESSION['password_creation_required'] === true;
?>

<link rel="stylesheet" href="/assets/css/perfil.css">
<link rel="stylesheet" href="/assets/css/toggle-switch.css">

<div class="perfil-box">
    <h2>Mi Perfil</h2>
    <div class="perfil-contenido">
        <div class="perfil-avatar-wrapper">
            <img src="<?= htmlspecialchars($url_avatar_final) ?>" alt="Avatar de usuario" class="perfil-avatar-img" id="mainAvatarImg">
            <div class="perfil-avatar-overlay">
                <span class="avatar-action-icon" id="abrirModalAvatar" title="Cambiar avatar">✏️</span>
                <span class="avatar-action-icon" id="verAvatarCompleto" title="Ver imagen completa">👁️</span>
            </div>
        </div>

        <div class="perfil-info">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) . ' ' . htmlspecialchars($usuario['apellido']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
            <?php // ✅ CORRECCIÓN: Comprobamos si hay una descripción antes de mostrarla ?>
				<?php if (!empty($usuario['descripcion'])): ?>
					<p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($usuario['descripcion'])) ?></p>
				<?php endif; ?>
		</div>
    </div>
</div>

<div class="modal-overlay" id="modalAvatar">
    <div class="modal-contenido">
        <button class="modal-close" id="cerrarModalAvatar">&times;</button>
        <h3>Elige tu Avatar</h3>

        <div class="modal-tabs">
            <button class="tab-link active" data-tab="tab-prediseñados">Prediseñados</button>
            <button class="tab-link" data-tab="tab-creaciones">Mis Creaciones</button>
            <button class="tab-link" data-tab="tab-ia">Crear con IA</button>
        </div>

        <div id="tab-prediseñados" class="modal-tab-content active">
            <div class="avatar-grid">
                <?php foreach ($avatares_prediseñados_thumbs as $thumb_path): 
                    $full_image_url = str_replace('/thumbs', '', str_replace(ROOT_PATH . '/public', '', $thumb_path));
                ?>
                    <img src="<?= str_replace(ROOT_PATH . '/public', '', $thumb_path) ?>" 
                         data-full-image-url="<?= $full_image_url ?>" 
                         class="avatar-seleccionable" alt="Avatar prediseñado">
                <?php endforeach; ?>
            </div>
        </div>

        <div id="tab-creaciones" class="modal-tab-content">
            <div class="avatar-grid" id="creacionesGrid">
                 <?php if (empty($mis_creaciones_full)): ?>
                    <p>Aún no has creado ningún avatar con la IA.</p>
                <?php else: ?>
                    <?php foreach ($mis_creaciones_full as $creacion): ?>
                        <div class="avatar-card-manage">
                            <img src="/assets/img/avatars/thumbs/users/<?= htmlspecialchars($creacion['nombre_archivo']) ?>" 
                                 data-full-image-url="/assets/img/avatars/users/<?= htmlspecialchars($creacion['nombre_archivo']) ?>" 
                                 class="avatar-seleccionable">
                            <div class="avatar-actions-small">
                                <span class="avatar-action-icon-small view-created-avatar" title="Ver" data-full-image-url="/assets/img/avatars/users/<?= htmlspecialchars($creacion['nombre_archivo']) ?>">👁️</span>
                                <span class="avatar-action-icon-small delete-created-avatar" title="Eliminar" data-avatar-id="<?= $creacion['id'] ?>">🗑️</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="tab-ia" class="modal-tab-content">
            <table class="tabla-estadisticas">
                <tbody>
                    <tr><td>Intentos Disponibles:</td><td><strong><?= htmlspecialchars($intentos_disponibles) ?></strong></td></tr>
                </tbody>
            </table>
            <form id="formCrearAvatar">
                <div class="toggle-switch-container">
                    <input type="checkbox" id="mejorar_prompt_ia" name="mejorar_prompt_ia" class="toggle-switch-checkbox" checked>
                    <label for="mejorar_prompt_ia" class="toggle-switch-label"></label>
                    <p>Mejorar prompt con IA</p>
                </div>
                <textarea name="prompt_ia" id="prompt_ia" placeholder="Ej: Un zorro mágico leyendo un libro" rows="2" maxlength="700"></textarea>
                <?php foreach ($opciones_ia as $key => $data): ?>
                    <label for="<?= $key ?>"><?= $data['label'] ?>:</label>
                    <select name="<?= $key ?>" id="<?= $key ?>">
                        <option value="">(Opcional)</option>
                        <?php foreach ($data['options'] as $option): ?>
                            <option value="<?= htmlspecialchars($opcion['keyword']) ?>"><?= htmlspecialchars($option['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>
                <button type="submit" class="btn-perfil">Generar</button>
            </form>
            <div id="resultado-ia" class="resultado-ia-container"></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalVerAvatar">
    <div class="modal-view-content">
        <button class="modal-close" id="cerrarModalVerAvatar">&times;</button>
        <img src="" alt="Avatar en tamaño completo" id="imagenCompletaAvatar">
    </div>
</div>

<div class="modal-overlay" id="modalConfirmarEliminar">
    <div class="modal-contenido">
        <h4>Confirmar Eliminación</h4>
        <p>¿Estás seguro de que quieres eliminar este avatar de forma permanente?</p>
        <div class="modal-acciones">
            <button id="cancelarEliminarBtn" class="btn-perfil">Cancelar</button>
            <button id="confirmarEliminarBtn" class="btn-perfil">Sí, Eliminar</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalCrearPassword" style="<?= $show_password_modal ? 'display: flex;' : 'display: none;' ?>">
    <div class="modal-contenido">
        <button class="modal-close" id="cerrarModalCrearPassword">&times;</button>
        <h3>¡Bienvenido! Unifica tu Cuenta</h3>
        <p>Para poder acceder también con tu email y contraseña, por favor, crea una contraseña para tu cuenta de la biblioteca.</p>
        <form action="/form-handler.php" method="POST">
            <input type="hidden" name="action" value="crear_password">
            <label for="password">Nueva Contraseña:</label>
            <input type="password" name="password" id="password" required>
            <label for="confirmar_password">Confirmar Contraseña:</label>
            <input type="password" name="confirmar_password" id="confirmar_password" required>
            <button type="submit" class="btn-perfil">Guardar Contraseña y Continuar</button>
        </form>
        <a href="#" id="ignorarUnificacionBtn">No volver a recordar</a>
    </div>
</div>

<script src="/assets/js/debug-logger.js" defer></script>
<script src="/assets/js/modal-handler.js" defer></script>
<script src="/assets/js/avatar-manager.js" defer></script>
<script src="/assets/js/ia-form-handler.js" defer></script>