<?php
/**
 * @var array $row Datos del libro
 */
?>
<div class="card libro-card">
  <img src="<?= base_url('assets/upload/' . $row['imagenLibro']); ?>" alt="Portada de <?= esc($row['nombreLibro']) ?>" class="card-img-top">
  <div class="card-body text-center">
      <h6 class="card-title"><?= esc($row['nombreLibro']) ?></h6>
      <p class="card-text text-muted"><?= esc($row['autor_formateado']) ?></p>
      <p class="card-text text-muted">$<?= esc($row['precioLibro']) ?></p>
      <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($row['descripcionLibro']) ?>">Leer sinopsis</span>
  </div>
</div>
