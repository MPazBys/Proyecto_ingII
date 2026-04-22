<div class="form-wrapper bg-dark bg-opacity-25">
    <p class="titulo-seccion">📚 Registro de Libros</p>

        <?php echo form_open_multipart('insertar_libro') ?>
             <?php if (isset($error_login)) : ?>
                <div class="alert alert-danger">
                <?= esc($error_login) ?>
                </div>
            <?php endif; ?>
            
                <div class="form-group">
                <label for="titulo">Titulo</label>
                    <?php echo form_input([
                        'name'        => 'titulo',
                        'id'          => 'titulo',
                        'type'        => 'text',
                        'class'       => 'form-control',
                        'placeholder' => 'Ingrese el titulo del libro',
                        'value'       => set_value('titulo')
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
                            $idAutor = $row['idAutor'];
                            $nombreAutor = $row['nombreAutor'];
                            $lista[$idAutor] = $nombreAutor;
                        }
                        echo form_dropdown('autor', $lista, '0', 'class="form-control"');
                        ?>
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
                        'placeholder' => 'Ingrese la descripcion del libro',
                        'value'       => set_value('descripcion')
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
                        'placeholder' => 'Ingrese el precio del libro',
                        'value'       => set_value('precio')
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
                        'placeholder' => 'Ingrese el stock del libro',
                        'value'       => set_value('stock')
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
                        'placeholder' => 'Ingrese la imagen del libro',
                        'value'       => set_value('imagen')
                    ]); ?>

                    <div class="img-preview-container">
                        <img id="preview-imagen" alt="Vista previa de imagen">
                    </div>

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
                            $idCategoria = $row['idCategoria'];
                            $nombreCategoria = $row['nombreCategoria'];
                            $lista[$idCategoria] = $nombreCategoria;
                        }
                        echo form_dropdown('categoria', $lista, '0', 'class="form-control"');
                        ?>
                    <?php if (isset($validation) && $validation->hasError('categoria')): ?>
                    <div class="mt-1 fw-bold text-danger alert alert-danger">
                        <?= $validation->getError('categoria'); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-3">
                    <?php echo form_submit('Agregar', 'Agregar', "class='btn btn-success'"); ?>
                </div>
            <?php echo form_close(); ?>
        
</div>


<script>
    const inputImagen = document.getElementById('imagen');
    const preview = document.getElementById('preview-imagen');

    inputImagen.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.setAttribute('src', e.target.result);
                preview.style.display = 'block';
            }

            reader.readAsDataURL(file);
        }
    });
</script>

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