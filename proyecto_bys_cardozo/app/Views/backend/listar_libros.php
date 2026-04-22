<div class="contenedor-wrapper">
    <p class="titulo-seccion">Listado de libros</p>

    <div class="row mb-4">
        <div class="col-lg-12">
            <?= form_open('buscar_admin', ['method' => 'get', 'class' => 'd-flex justify-content-center align-items-center gap-2']) ?>
                
                <label class="form-label fw-bold mb-0">Filtrar por:</label>
                
                <select name="filtro_por" class="form-select w-auto">
                    <option value="nombre" <?= (isset($_GET['filtro_por']) && $_GET['filtro_por'] == 'nombre') ? 'selected' : '' ?>>Nombre</option>
                    <option value="autor" <?= (isset($_GET['filtro_por']) && $_GET['filtro_por'] == 'autor') ? 'selected' : '' ?>>Autor</option>
                    <option value="genero" <?= (isset($_GET['filtro_por']) && $_GET['filtro_por'] == 'genero') ? 'selected' : '' ?>>Género</option>
                </select>

                <?= form_input([
                    'name'        => 'busqueda',
                    'type'        => 'search',
                    'class'       => 'form-control w-25',
                    'placeholder' => '¿Qué estás buscando?',
                    'value'       => isset($_GET['busqueda']) ? $_GET['busqueda'] : ''
                ]) ?>
                
                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Aplicar
                </button>

                <a href="<?= base_url('gestionar') ?>" class="btn btn-secondary">
                    Limpiar
                </a>
            <?= form_close() ?>
        </div>
    </div>

    <div class="row mb-4">
        <a class="btn btn-secondary" href="<?= base_url('agregar'); ?>">Agregar libro</a>
    </div>

    <div class="table-responsive">
        <?php if (empty($libro)): ?>
            <div class="text-center w-100">
                <p class="fs-4 fw-bold text-danger alert alert-danger">No se encontraron libros con esos criterios.</p>
            </div>
        <?php else: ?>
            <table id="mytable" class="table table-bordered table-striped table-hover">
                <thead class="text-center table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Autor</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoria</th>
                        <th>Etiqueta</th>
                        <th>Imagen</th>
                        <th>Editar</th>
                        <th>Eliminar/Activar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($libro as $row) { ?>
                        <tr>
                            <td><?php echo $row['nombreLibro']; ?></td>
                            <td><?php echo $row['nombreAutor']; ?></td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo $row['descripcionLibro']; ?>
                            </td>
                            <td>$<?php echo $row['precioLibro']; ?></td>
                            <td><?php echo $row['stockLibro']; ?></td>
                            <td><?php echo $row['nombreCategoria']; ?></td>
                            <td><?php echo $row['nombreEtiqueta']; ?></td>
                            <td class="text-center">
                                <img src="<?php echo base_url('assets/upload/'.$row['imagenLibro']); ?>" alt="" height="80" width="60">
                            </td>
                            <td class="text-center">
                                <a href="<?php echo base_url('editar/'.$row['idLibro']); ?>" class="btn btn-success btn-sm">Editar</a>
                            </td>
                            <td class="text-center">
                                <?php if($row['estado'] == 1) { ?>
                                    <a class="btn btn-danger btn-sm" href="<?php echo base_url('eliminar/'.$row['idLibro']); ?>">Eliminar</a>
                                <?php } else { ?>
                                    <a class="btn btn-warning btn-sm" href="<?php echo base_url('activar/'.$row['idLibro']); ?>">Activar</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('mensaje')): ?>
<script>
Swal.fire({
  title: '¡Éxito!',
  icon: 'success',
  text: '<?= session()->getFlashdata('mensaje'); ?>',
  confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>