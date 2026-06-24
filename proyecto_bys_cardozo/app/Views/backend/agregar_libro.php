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
                        // Cargar listado de autores en formato 'Nombres Apellidos' y conservar la selección si falla la validación
                        $listaAutores['0'] = 'Seleccione autor';
                        foreach ($autores as $row) {
                            $idAutor = $row['idAutor'];
                            $nombreCompleto = $row['nombreAutor'] . ' ' . $row['apellidoAutor'];
                            $listaAutores[$idAutor] = $nombreCompleto;
                        }
                        $selectedAutor = set_value('autor') ?: '0';
                        echo form_dropdown('autor', $listaAutores, $selectedAutor, 'class="form-control" id="autor"');
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
                        'type'        => 'DECIMAL',
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
                <label for="fechaedicion">Fecha de Edición</label>
                    <?php 
                        // Crear listado de años desde el actual hasta 1900 para selección
                        $years = [];
                        $currentYear = (int)date('Y');
                        for ($y = $currentYear; $y >= 1900; $y--) {
                            $years[$y] = $y;
                        }
                        // Conservar el año seleccionado si la validación falla
                        $selectedYear = set_value('fechaedicion') ?: $currentYear;
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
                        // Cargar listado de categorías y conservar la selección tras fallar la validación
                        $listaCat['0'] = 'Seleccione categoria';
                        foreach ($categorias as $row) {
                            $idCategoria = $row['idCategoria'];
                            $nombreCategoria = $row['nombreCategoria'];
                            $listaCat[$idCategoria] = $nombreCategoria;
                        }
                        $selectedCategoria = set_value('categoria') ?: '0';
                        echo form_dropdown('categoria', $listaCat, $selectedCategoria, 'class="form-control" id="categoria"');
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