<?php
// /public/paginas/insignias.php (Versión 2, Corregida)

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php?p=login_form');
    exit;
}

try {
    $stmt_todas = $pdo->query("SELECT * FROM insignias WHERE activa = 1 ORDER BY categoria, rareza, nombre");
    $todas_las_insignias_raw = $stmt_todas->fetchAll(PDO::FETCH_ASSOC);

    $stmt_usuario = $pdo->prepare("SELECT id_insignia FROM usuarios_insignias WHERE id_usuario = ?");
    $stmt_usuario->execute([$_SESSION['user_id']]);
    $insignias_ganadas_ids = $stmt_usuario->fetchAll(PDO::FETCH_COLUMN);

    $insignias_por_categoria = [];
    foreach ($todas_las_insignias_raw as $insignia) {
        $insignias_por_categoria[$insignia['categoria']][] = $insignia;
    }

} catch (PDOException $e) {
    log_system_event("Error de BD al cargar insignias.", ['id_usuario' => $_SESSION['user_id'], 'error' => $e->getMessage()]);
    die("Error: No se pudieron cargar las insignias. Por favor, intenta de nuevo más tarde.");
}

function render_insignia_card($insignia, $is_locked = false) {
    $rareza_class = 'rareza-' . strtolower(str_replace(' ', '-', $insignia['rareza']));
    
    $thumbnail_url = BASE_URL . 'assets/img/insignias/thumbs/' . $insignia['imagen'];

    if ($is_locked) {
        $modal_image_url = BASE_URL . 'image_protector.php?img=' . urlencode($insignia['imagen']) . '&contexto=insignias';
        $descripcion = htmlspecialchars($insignia['pista'] ?? 'Sigue explorando para desbloquear.');
    } else {
        $modal_image_url = BASE_URL . 'assets/img/insignias/' . $insignia['imagen'];
        $descripcion = htmlspecialchars($insignia['descripcion']);
    }

    echo '
    <div class="insignia-card ' . $rareza_class . ($is_locked ? ' locked' : '') . '" 
         data-bs-toggle="modal" data-bs-target="#viewInsigniaModal"
         data-nombre="' . htmlspecialchars($insignia['nombre']) . '" 
         data-descripcion="' . $descripcion . '"
         data-imagen-url="' . htmlspecialchars($modal_image_url) . '"
         data-ganada="' . ($is_locked ? 'false' : 'true') . '">
        
        <div>
            <img src="' . htmlspecialchars($thumbnail_url) . '" alt="' . htmlspecialchars($insignia['nombre']) . '">
            <p class="insignia-nombre">' . htmlspecialchars($insignia['nombre']) . '</p>
        </div>

        <div>
            <span class="insignia-puntos">' . htmlspecialchars($insignia['puntos_recompensa']) . ' pts</span>
        </div>
    </div>';
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/insignias_styles.css?v=<?php echo time(); ?>">

<div class="container insignias-container mt-4">

    <div class="insignias-seccion">
        <h2 class="seccion-titulo">Mis Logros</h2>
        <hr>
        <?php
        $logros_encontrados = false;
        // La lógica para mostrar insignias ganadas (incluso inactivas) se añade aquí.
        $stmt_ganadas = $pdo->prepare(
            "SELECT i.* FROM insignias i JOIN usuarios_insignias ui ON i.id_insignia = ui.id_insignia WHERE ui.id_usuario = ? ORDER BY i.categoria, i.rareza, i.nombre"
        );
        $stmt_ganadas->execute([$_SESSION['user_id']]);
        $insignias_ganadas_detalles = $stmt_ganadas->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($insignias_ganadas_detalles)) {
            $logros_encontrados = true;
            $logros_por_categoria = [];
            foreach ($insignias_ganadas_detalles as $insignia) {
                $logros_por_categoria[$insignia['categoria']][] = $insignia;
            }

            foreach ($logros_por_categoria as $categoria => $insignias) {
                echo '<h4 class="categoria-titulo">' . htmlspecialchars($categoria) . '</h4>';
                echo '<div class="insignia-grid">';
                foreach ($insignias as $insignia) {
                    render_insignia_card($insignia, false);
                }
                echo '</div>';
            }
        }
        
        if (!$logros_encontrados) {
            echo '<p class="text-center text-muted w-100">Aún no has ganado ninguna insignia. ¡Explora los desafíos pendientes!</p>';
        }
        ?>
    </div>

    <div class="insignias-seccion mt-5">
        <h2 class="seccion-titulo">Desafíos Pendientes</h2>
        <hr>
        <?php
        // La lógica para mostrar desafíos pendientes se mantiene, pero ahora se usa la consulta inicial.
        foreach ($insignias_por_categoria as $categoria => $insignias) {
            $desafios_en_categoria = array_filter($insignias, fn($i) => !in_array($i['id_insignia'], $insignias_ganadas_ids));
            if (!empty($desafios_en_categoria)) {
                echo '<h4 class="categoria-titulo">' . htmlspecialchars($categoria) . '</h4>';
                echo '<div class="insignia-grid">';
                foreach ($desafios_en_categoria as $insignia) {
                    render_insignia_card($insignia, true);
                }
                echo '</div>';
            }
        }
        ?>
    </div>
</div>

<?php
include_once ROOT_PATH . '/public/includes/modals/modal_view_insignia.php';
?>
<script src="<?php echo BASE_URL; ?>assets/js/insignias-main.js?v=<?php echo time(); ?>"></script>