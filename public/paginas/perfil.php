<?php
// /public/paginas/perfil.php

// Seguridad: Redirigir si no hay una sesión activa.
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php?p=login_form');
    exit;
}

// ---- Carga de Datos del Usuario ----
// Obtenemos los datos más actualizados de la base de datos para este usuario.
// Esto asegura que si un admin cambia su rango, se refleje inmediatamente.
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si por alguna razón el usuario no existe en la BD, cerramos su sesión.
    if (!$user) {
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }

} catch (PDOException $e) {
    // Si hay un error de BD, mostramos un mensaje genérico.
    log_system_event("Error de BD al cargar datos del perfil.", ['id_usuario' => $_SESSION['user_id'], 'error' => $e->getMessage()]);
    // Idealmente, aquí se mostraría una página de error más amigable.
    die("Error: No se pudieron cargar los datos del perfil. Por favor, intenta de nuevo más tarde.");
}

// ---- Preparación de la URL del Avatar ----
// Usaremos una función helper para determinar qué avatar mostrar.
// Esta función la crearemos en el futuro, por ahora usamos una lógica simple.
$avatar_url = BASE_URL . 'assets/img/avatars/default.png'; // Avatar por defecto
if (!empty($user['avatar_seleccionado'])) {
    // Asumimos que la ruta completa está guardada en la base de datos por ahora.
    $avatar_url = BASE_URL . $user['avatar_seleccionado']; 
} elseif (!empty($user['avatar_google'])) {
    $avatar_url = $user['avatar_google'];
}

?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/perfil_styles.css?v=<?php echo time(); ?>">

<div class="container profile-container">
    
    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <div class="profile-avatar-wrapper">
                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar del usuario" class="profile-avatar img-fluid rounded-circle">
                <div class="avatar-overlay">
                    <button class="btn btn-sm btn-light" id="btn-edit-avatar" data-bs-toggle="modal" data-bs-target="#avatarManagerModal">
                        <i class="fas fa-pencil-alt"></i> </button>
                    <button class="btn btn-sm btn-light" id="btn-view-avatar" data-bs-toggle="modal" data-bs-target="#viewAvatarModal">
                        <i class="fas fa-eye"></i> </button>
                </div>
            </div>
            <h4 class="mt-3"><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></h4>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($user['rango'])); ?></span>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Editar Perfil</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>form-handler.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user['apellido']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Sobre mí</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($user['descripcion']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tema" class="form-label">Tema Visual</label>
                            <select class="form-select" id="tema" name="tema">
                                <option value="default" <?php echo ($user['tema'] === 'default') ? 'selected' : ''; ?>>Claro</option>
                                <option value="neon_dark" <?php echo ($user['tema'] === 'neon_dark') ? 'selected' : ''; ?>>Oscuro Neón</option>
                                </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// --- Inclusión de los Modales ---
// Incluimos todos los modales que esta página podría necesitar.
include_once ROOT_PATH . '/public/includes/modals/modal_avatar_manager.php';
include_once ROOT_PATH . '/public/includes/modals/modal_view_avatar.php';

// El modal de crear contraseña solo se carga si el flag de sesión existe.
if (isset($_SESSION['password_creation_required'])) {
    include_once ROOT_PATH . '/public/includes/modals/modal_create_password.php';
}
?>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script src="<?php echo BASE_URL; ?>assets/js/perfil-main.js?v=<?php echo time(); ?>"></script>