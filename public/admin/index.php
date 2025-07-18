<?php
// Solo necesita llamar al "guardia", que se encarga de todo.
require_once __DIR__ . '/includes/auth.php';
$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $page_title ?> - Panel de Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
  <div class="admin-wrapper">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <main class="admin-main">
      <?php include __DIR__ . '/includes/header.php'; ?>
      <section class="admin-content">
        <h1>Bienvenido al Panel</h1>
        <p>Desde aquí podés administrar usuarios, la configuración del motor de IA y monitorear el sistema.</p>
      </section>
    </main>
  </div>
</body>
</html>