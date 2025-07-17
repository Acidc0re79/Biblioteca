<?php
require_once __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="admin-layout">
    <?php include __DIR__ . '/includes/nav.php'; ?>

    <section class="admin-content">
      <h1>Bienvenido al Panel</h1>
      <p>Desde aquí podés administrar usuarios, insignias, puntos y más.</p>
    </section>
  </main>
</body>
</html>
