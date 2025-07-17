<?php
require_once __DIR__ . '/../config/init.php';

$rutas_paginas = glob(ROOT_PATH . '/public/paginas/*.php');
$paginas_permitidas = array_map(fn($ruta) => basename($ruta, '.php'), $rutas_paginas);

$pagina_solicitada = $_GET['p'] ?? 'main';

$paginas_protegidas = ['perfil', 'otra_pagina_privada'];
if (in_array($pagina_solicitada, $paginas_protegidas) && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php?p=login_form');
    exit;
}

$theme = $_SESSION['tema'] ?? 'default';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="<?= htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Digital SYS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/estructura.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/header.css?v=<?= time(); ?>">
    <link rel="stylesheet" id="theme-style" href="<?= BASE_URL; ?>themes/<?= htmlspecialchars($theme); ?>/theme.css?v=<?= time(); ?>">
    
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/logs_viewer.css?v=<?= time(); ?>">
</head>
<body>
    <div id="main-container">
        <?php include ROOT_PATH . '/public/includes/header.php'; ?>
        <?php include ROOT_PATH . '/public/includes/nav.php'; ?>

        <div id="content-wrap">
            <main class="container-fluid flex-grow-1">
                <?php
                if (in_array($pagina_solicitada, $paginas_permitidas)) {
                    include ROOT_PATH . '/public/paginas/' . $pagina_solicitada . '.php';
                } else {
                    include ROOT_PATH . '/public/paginas/404.php';
                }
                ?>
            </main>
        </div>

        <?php include ROOT_PATH . '/public/includes/footer.php'; ?>
    </div>

    <script> const BASE_URL = '<?= BASE_URL; ?>'; </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL; ?>assets/js/logs_viewer.js?v=<?= time(); ?>"></script>
</body>
</html>