<div class="contenedor-wrapper">
    <h1 class="titulo-seccion text-center mb-4">Catálogo de Libros</h1>

    <div class="table-responsive">
        <?php if (!empty($libro) && is_array($libro)): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Autor</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoria</th>
                        <th>Imagen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($libro as $row): ?>
                        <tr>
                            <td><?php echo $row['nombreLibro']; ?></td>
                            <td><?php echo $row['nombreAutor']; ?></td>
                            <td><?php echo $row['descripcionLibro']; ?></td>
                            <td>$<?php echo $row['precioLibro']; ?></td>
                            <td><?php echo $row['stockLibro']; ?></td>
                            <td><?php echo $row['nombreCategoria']; ?></td>
                            <td><img src="<?php echo base_url('assets/upload/'.$row['imagenLibro']); ?>" alt="" height="125" width="100"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No hay libros cargados actualmente.</div>
            </div>
        <?php endif ?>
    </div>
</div>