<?php
$id_usuario = $_SESSION['usuario_id'] ?? null;

$stmt = $pdo->prepare("SELECT id_insignia FROM usuarios_insignias WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$insignias_usuario = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<link rel="stylesheet" href="/assets/css/insignias.css">

<!-- Protección contra clic derecho y arrastre -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.insignia-img img').forEach(img => {
      img.addEventListener('contextmenu', e => e.preventDefault());
      img.setAttribute('draggable', 'false');
    });
  });
</script>

<section class="insignias-grid">
  <?php
  $insignias = $pdo->query("SELECT * FROM insignias ORDER BY puntos_requeridos DESC")->fetchAll(PDO::FETCH_ASSOC);

  $pos = 0;
  foreach ($insignias as $insignia):
    $lograda = in_array($insignia['id_insignia'], $insignias_usuario);
    $pos++;

    $clase_podio = '';
    if ($pos == 1) $clase_podio = 'podio-1';
    elseif ($pos == 2) $clase_podio = 'podio-2';
    elseif ($pos == 3) $clase_podio = 'podio-3';
  ?>
    <div class="insignia-card <?= $lograda ? 'lograda' : '' ?> <?= $clase_podio ?>">
      <div class="insignia-img">
        <?php if ($lograda): ?>
          <a href="#" onclick="mostrarModal('<?= htmlspecialchars($insignia['nombre']) ?>', '<?= htmlspecialchars($insignia['descripcion']) ?>', '/assets/img/insignias/<?= htmlspecialchars($insignia['imagen_url']) ?>'); return false;">
            <img 
              src="/assets/img/insignias/thumbs/<?= htmlspecialchars($insignia['imagen_url']) ?>" 
              alt="<?= htmlspecialchars($insignia['nombre']) ?>" 
              loading="lazy"
            >
          </a>
          <div class="insignia-tick">✔</div>
        <?php else: ?>
          <img 
            src="/assets/img/insignias/thumbs/<?= htmlspecialchars($insignia['imagen_url']) ?>" 
            alt="<?= htmlspecialchars($insignia['nombre']) ?>" 
            loading="lazy"
          >
        <?php endif; ?>
      </div>
      <h3><?= htmlspecialchars($insignia['nombre']) ?></h3>
      <p><?= htmlspecialchars($insignia['descripcion']) ?></p>
      <span class="puntos">+<?= $insignia['puntos_requeridos'] ?> pts</span>
    </div>
  <?php endforeach; ?>
</section>

<!-- MODAL para ver imagen ampliada -->
<div id="insigniaModal" class="modal">
  <div class="modal-content">
    <span class="modal-close" onclick="cerrarModal()">&times;</span>
    <img id="modal-img" src="" alt="">
    <h3 id="modal-nombre"></h3>
    <p id="modal-descripcion"></p>
    <a id="modal-descarga" href="#" download class="modal-descargar">Descargar</a>
  </div>
</div>

<script>
function mostrarModal(nombre, descripcion, imagenUrl) {
  document.getElementById('modal-img').src = imagenUrl;
  document.getElementById('modal-img').alt = nombre;
  document.getElementById('modal-nombre').innerText = nombre;
  document.getElementById('modal-descripcion').innerText = descripcion;
  document.getElementById('modal-descarga').href = imagenUrl;
  document.getElementById('insigniaModal').style.display = 'flex';
}

function cerrarModal() {
  document.getElementById('insigniaModal').style.display = 'none';
}
</script>
