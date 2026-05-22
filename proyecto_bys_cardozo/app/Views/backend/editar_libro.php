<div class="form-wrapper">
    <h1 class="titulo-seccion">Edición de libros</h1>

        <?php echo form_open_multipart("actualizar"); ?>
        <div class="form-group">
            <label for="titulo">Titulo</label>
            <?php echo form_input([
                'name'        => 'titulo',
                'id'          => 'titulo',
                'type'        => 'text',
                'class'       => 'form-control',
                'autofocus'   => 'autofocus',
                'value'       => set_value('titulo') ?: $libro['nombreLibro']
            ]); ?>
            <?php if (isset($validation) && $validation->hasError('titulo')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('titulo'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="autor">Autor</label>
            <?php 
                // Cargar autores en formato 'Nombres Apellidos' y conservar la selección si falla la validación
                $listaAutores['0'] = 'Seleccione autor';
                foreach ($autores as $row) {
                    $listaAutores[$row['idAutor']] = $row['nombreAutor'] . ' ' . $row['apellidoAutor'];
                }
                $sel = set_value('autor') ?: $libro['idAutor'];
                echo form_dropdown('autor', $listaAutores, $sel, 'class="form-control" id="autor"'); ?>
            <?php if (isset($validation) && $validation->hasError('autor')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('autor'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="descripcion">Descripcion</label>
            <?php echo form_input([
                'name'        => 'descripcion',
                'id'          => 'descripcion',
                'type'        => 'text',
                'class'       => 'form-control',
                'autofocus'   => 'autofocus',
                'value'       => set_value('descripcion') ?: $libro['descripcionLibro']
            ]); ?>
            <?php if (isset($validation) && $validation->hasError('descripcion')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('descripcion'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="precio">Precio</label>
            <?php echo form_input([
                'name'        => 'precio',
                'id'          => 'precio',
                'type'        => 'number',
                'class'       => 'form-control',
                'autofocus'   => 'autofocus',
                'value'       => set_value('precio') ?: $libro['precioLibro']
            ]); ?>
            <?php if (isset($validation) && $validation->hasError('precio')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('precio'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="stock">Stock</label>
            <?php echo form_input([
                'name'        => 'stock',
                'id'          => 'stock',
                'type'        => 'number',
                'class'       => 'form-control',
                'autofocus'   => 'autofocus',
                'value'       => set_value('stock') ?: $libro['stockLibro']
            ]); ?>
            <?php if (isset($validation) && $validation->hasError('stock')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('stock'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="fechaedicion">Fecha de Edición</label>
            <?php 
                // Crear listado de años para la edición del libro, soportando años históricos si fuera necesario
                $years = [];
                $currentYear = (int)date('Y');
                $startYear = 1900;
                $valYear = (isset($libro['fechaEdicion']) && is_numeric($libro['fechaEdicion'])) ? (int)$libro['fechaEdicion'] : $currentYear;
                if ($valYear < $startYear) {
                    $startYear = $valYear;
                }
                for ($y = $currentYear; $y >= $startYear; $y--) {
                    $years[$y] = $y;
                }
                $selectedYear = set_value('fechaedicion') ?: $valYear;
                echo form_dropdown('fechaedicion', $years, $selectedYear, 'class="form-control" id="fechaedicion"');
            ?>
            <?php if (isset($validation) && $validation->hasError('fechaedicion')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('fechaedicion'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="imagen">Imagen</label>        
            <?php echo form_input([
                'name'        => 'imagen',
                'id'          => 'imagen',
                'type'        => 'file',
                'class'       => 'form-control',
            ]); ?>
            <img id="prev-imagen" src="<?= base_url('assets/upload/'.$libro['imagenLibro']); ?>" alt="Vista previa" height="150" width="125">
            <?php if (isset($validation) && $validation->hasError('imagen')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('imagen'); ?>
            </div>
            <?php endif; ?>
        </div>


        <div class="form-group">
        <label for="categoria">Categoria</label>
            <?php 
                // Cargar categorías y conservar la selección si falla la validación
                $listaCat['0'] = 'Seleccione categoria';
                foreach ($categorias as $row) {
                    $listaCat[$row['idCategoria']] = $row['nombreCategoria'];
                }
                $sel = set_value('categoria') ?: $libro['idCategoria'];
                echo form_dropdown('categoria', $listaCat, $sel, 'class="form-control" id="categoria"'); ?>
            <?php if (isset($validation) && $validation->hasError('categoria')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('categoria'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="etiqueta">Etiqueta</label>
            <?php 
                // Cargar etiquetas y conservar la selección si falla la validación
                $listaEtiq['0'] = 'Seleccione etiqueta';
                foreach ($etiquetas as $row) {
                    $listaEtiq[$row['idEtiqueta']] = $row['nombre'];
                }
                $sel = set_value('etiqueta') ?: $libro['idEtiqueta'];
                echo form_dropdown('etiqueta', $listaEtiq, $sel, 'class="form-control" id="etiqueta"'); ?>
            <?php if (isset($validation) && $validation->hasError('etiqueta')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('etiqueta'); ?>
            </div>
            <?php endif; ?>
        </div>

        <?php echo form_hidden('id', $libro['idLibro']); ?>

        <div class="text-center mt-3">
            <?php echo form_submit('Modificar', 'Modificar', "class='btn btn-success'"); ?>
        </div>

    <?php echo form_close(); ?>

</div>

<script>
document.getElementById('imagen').addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('prev-imagen');

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
