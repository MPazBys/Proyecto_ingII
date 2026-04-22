<div class="contenedor-wrapper">
  <p class="titulo-catalogo">Lista de Libros</p>

  <div class="mb-4 text-center">
    <?php echo form_open('productos', ['method' => 'get', 'class' => 'd-inline-block p-3 rounded shadow-sm bg-light']); ?>
      
      <label class="form-label fw-bold me-2">Filtrar por:</label>
      
      <select name="filtro_por" class="form-select d-inline-block w-auto">
          <option value="nombre" <?= (isset($filtrado['tipo']) && $filtrado['tipo'] == 'nombre') ? 'selected' : '' ?>>Nombre del Libro</option>
          <option value="autor" <?= (isset($filtrado['tipo']) && $filtrado['tipo'] == 'autor') ? 'selected' : '' ?>>Autor</option>
          <option value="genero" <?= (isset($filtrado['tipo']) && $filtrado['tipo'] == 'genero') ? 'selected' : '' ?>>Categoría</option>
      </select>

      <input type="text" name="clave" class="form-control d-inline-block w-25" 
             placeholder="Palabra clave..." 
             value="<?= $filtrado['clave'] ?? '' ?>">

      <button type="submit" class="btn btn-primary">Aplicar</button>
      
      <a href="<?= base_url('productos') ?>" class="btn btn-secondary">Limpiar</a>

    <?php echo form_close(); ?>
  </div>

  <div class="d-flex justify-content-center gap-4 flex-wrap mt-5">
      <?php if (empty($libro)): ?>
        <div class="text-center w-100">
          <p class="fs-4 fw-bold text-danger alert alert-danger">No se encontró ningún libro con ese filtro.</p>
        </div>
      <?php else: ?>
        <?php foreach($libro as $row) { ?>
          <div class="card libro-card">
            <img src="<?php echo base_url('assets/upload/'.$row['imagenLibro']); ?>" alt="Portada" class="card-img-top">
            <div class="card-body text-center">
                <h6 class="card-title"><?php echo $row['nombreLibro']; ?></h6>
                <p class="card-text text-muted"><?php echo $row['nombreAutor']; ?></p>
                <p class="card-text text-muted"><?php echo "$" . $row['precioLibro']; ?></p>
                <p class="card-text text-muted"><?php echo "Categoría: " . $row['nombreCategoria']; ?></p>
                <p class="card-text text-muted"><?php echo "Disponibles: " . $row['stockLibro']; ?></p>
                
                <span class="btn btn-primary btn-sm" 
                    data-bs-toggle="tooltip" 
                    title="<?= esc($row['descripcionLibro']) ?>">
                    Leer sinopsis
                </span>
                
                <?php if(session('login')) {
                    echo form_open('add_cart', ['method' => 'post']);
                    echo form_hidden('id', $row['idLibro']);
                    echo form_hidden('titulo', $row['nombreLibro']);
                    echo form_hidden('precio', $row['precioLibro']);
                    echo form_submit('comprar', 'Añadir al carrito', "class='btn btn-success mt-3'");
                    echo form_close();
                } ?>
            </div>
          </div>
        <?php } ?>
      <?php endif; ?>
  </div>
</div>

<?php if (session()->getFlashdata('mensaje')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))
    });

  Swal.fire({
  title: '¡Éxito!',
  icon: 'success',
  text: '<?= session()->getFlashdata('mensaje'); ?>',
  confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('msj')): ?>
<script>
Swal.fire({
  title: 'Error!',
  icon: 'error',
  text: '<?= session()->getFlashdata('msj'); ?>',
  confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>

