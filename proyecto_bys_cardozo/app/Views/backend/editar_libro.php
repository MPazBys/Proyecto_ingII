<div class="form-wrapper">
    <h1 class="titulo-seccion">Edición de libros</h1>

    <div class="w-5 mx-auto">
        <?php echo form_open_multipart("actualizar"); ?>
        <div class="form-group">
            <label for="titulo">Titulo</label>
            <?php echo form_input([
                'name'        => 'titulo',
                'id'          => 'titulo',
                'type'        => 'text',
                'class'       => 'form-control',
                'autofocus'   => 'autofocus',
                'value'       => $libro['nombreLibro']
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
                $lista['0'] = 'Seleccione autor';
                foreach ($autores as $row) {
                    $lista[$row['idAutor']] = $row['nombreAutor'];
                }
                $sel = $libro['idAutor'];
                echo form_dropdown('autor', $lista, $sel, 'class="form-control"'); ?>
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
                'value'       => $libro['descripcionLibro']
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
                'value'       => $libro['precioLibro']
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
                'value'       => $libro['stockLibro']
            ]); ?>
            <?php if (isset($validation) && $validation->hasError('stock')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('stock'); ?>
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
                $lista['0'] = 'Seleccione categoria';
                foreach ($categorias as $row) {
                    $lista[$row['idCategoria']] = $row['nombreCategoria'];
                }
                $sel = $libro['idCategoria'];
                echo form_dropdown('categoria', $lista, $sel, 'class="form-control"'); ?>
            <?php if (isset($validation) && $validation->hasError('categoria')): ?>
            <div class="mt-1 fw-bold text-danger alert alert-danger">
                <?= $validation->getError('categoria'); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="etiqueta">Etiqueta</label>
            <?php 
                $listaEtiq['0'] = 'Seleccione etiqueta';
                foreach ($etiquetas as $row) {
                    $listaEtiq[$row['idEtiqueta']] = $row['nombre'];
                }
                $sel = $libro['etiquetaLibro'];
                echo form_dropdown('etiqueta', $listaEtiq, $sel, 'class="form-control"'); ?>
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
