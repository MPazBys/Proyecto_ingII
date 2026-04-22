<?php $cart = \Config\Services::cart(); ?>

<div class="contenedor-wrapper">
    <p class="titulo-seccion">Carrito de compras</p>

    <div class="container mt-5">
    <div class="alert alert-info shadow-sm d-flex align-items-center rounded-pill p-3" role="alert">
        <i class="fa-solid fa-person-digging fs-3 me-3 text-primary"></i>
        <div>
            <h4 class="alert-heading mb-1 fw-bold text-primary">¡Sección en proceso!</h4>
            <p class="mb-0 text-muted">Estamos trabajando para traerte esta funcionalidad muy pronto. ¡Gracias por tu paciencia!</p>
        </div>
    </div>
    </div>

    <!-- <a href="<?= base_url('productos') ?>" class="btn btn-success" role="button">Continuar comprando</a>

    <?php if ($cart->contents() == NULL) { ?>
        <p class="titulos text-center alert alert-danger">El carrito está vacío</p>
    <?php } ?>

    <?php if ($cart1 = $cart->contents()): ?>
    <div class="table-responsive">
        <table id="mytable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>N° item</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $total = 0;
                    $i = 1;
                    foreach ($cart1 as $item): ?>

                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td>$<?php echo $item['price']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($item['qty'] > 1): ?>
                                    <a href="<?= base_url('disminuir_cantidad/'.$item['rowid']); ?>" class="btn btn-warning btn-sm">−</a>
                                <?php else: ?>
                                    <button class="btn btn-warning btn-sm" disabled>−</button>
                                <?php endif; ?>

                                <span class="mx-2"><?= $item['qty']; ?></span>

                                <?php if (isset($item['stockLibro']) && $item['qty'] < $item['stockLibro']): ?>
                                    <a href="<?= base_url('aumentar_cantidad/'.$item['rowid']); ?>" class="btn btn-warning btn-sm">+</a>
                                <?php else: ?>
                                    <button class="btn btn-warning btn-sm" disabled>+</button>
                                <?php endif; ?>

                            </div>
                        </td>
                        <td>$<?php echo $item['subtotal']; ?></td>
                        <td>
                            <?php echo anchor('eliminar_item/'.$item['rowid'], 'Eliminar', ['class' => 'btn btn-success']); ?>
                        </td>
                    </tr>

                <?php
                    $total += $item['subtotal'];
                    endforeach; ?>

                <tr>
                    <td>Total compra: $<?php echo $total; ?></td>
                    <td colspan="5" class="text-right">
                        <a href="<?php echo base_url('vaciar_carrito/all'); ?>" class="btn btn-danger">Vaciar carrito</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-6 mt-3">
            <div class="form-group">
                <label for="formaEnvio">Forma de Envío:</label>
                <select class="form-select mt-2" id="formaEnvio" name="formaEnvio" required>
                    <option value="" <?= set_select('selectedFormaEnvio', ''); ?>>Seleccione forma de envío</option> 
                    <option value="1" <?= set_select('selectedFormaEnvio', '1'); ?>>Retiro en sucursal</option> 
                    <option value="2" <?= set_select('selectedFormaEnvio', '2'); ?>>Envío a domicilio</option> 
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="formaPago">Forma de Pago:</label>
                <select class="form-select mt-2" id="formaPago" name="formaPago" required>
    <option value="" <?= set_select('selectedFormaPago', ''); ?>>Seleccione forma de pago</option> <?php foreach($formasPago as $fp): ?>
        <option value="<?= esc($fp['idPago']); ?>" <?= set_select('selectedFormaPago', $fp['idPago']); ?>> <?= esc($fp['nombrePago']); ?>
        </option>
    <?php endforeach; ?>
</select>
            </div>
        </div>
            
    </div>
    <button type="button" class="btn btn-success mt-4" data-bs-toggle="modal" data-bs-target="#confirmarCompraModal">Ordenar Compra</button>
    <?php endif; ?>
</div>

<div class="modal fade" id="confirmarCompraModal" tabindex="-1" aria-labelledby="confirmarCompraModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarCompraModalLabel">Confirmar Compra y Datos de Envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('procesar_finalizar_compra') ?>" method="post" novalidate>
                <div class="modal-body">

                    <p class="mt-2 texto-seccion">Información de contacto y envío</p>

                    <div id="domicilioFields" style="display: none;">
                        <div class="mb-3">
                            <label for="modal_domicilio">Domicilio</label>
                            <?php echo form_input([
                                'name'        => 'domicilio',
                                'id'          => 'modal_domicilio', 
                                'type'        => 'text',
                                'class'       => 'form-control',
                                'placeholder' => 'Ingrese su domicilio',
                                'value'       => set_value('domicilio', $persona['direccion'] ?? '') 
                            ]); ?>
                             <?php if (session('errors.domicilio')): ?>
                                <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.domicilio'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="modal_ciudad">Ciudad</label>
                            <?php echo form_input([
                                'name'        => 'ciudad',
                                'id'          => 'modal_ciudad', 
                                'type'        => 'text',
                                'class'       => 'form-control',
                                'placeholder' => 'Ingrese su ciudad',
                                'value'       => set_value('ciudad', $persona['ciudad'] ?? '')
                            ]); ?>
                            <?php if (session('errors.ciudad')): ?>
                                <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.ciudad'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="modal_provincia">Provincia</label>
                            <?php echo form_input([
                                'name'        => 'provincia',
                                'id'          => 'modal_provincia', 
                                'type'        => 'text',
                                'class'       => 'form-control',
                                'placeholder' => 'Ingrese su provincia',
                                'value'       => set_value('provincia', $persona['provincia'] ?? '')
                            ]); ?>
                            <?php if (session('errors.provincia')): ?>
                                <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.provincia'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_telefono">Teléfono</label>
                        <?php echo form_input([
                            'name'        => 'telefono',
                            'id'          => 'modal_telefono', 
                            'type'        => 'text', 
                            'class'       => 'form-control',
                            'placeholder' => 'Ingrese su teléfono',
                            'value'       => set_value('telefono', $persona['telefono'] ?? ''),
                            'required'    => 'required' 
                        ]); ?>
                        <?php if (session('errors.telefono')): ?>
                            <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.telefono'); ?></div>
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="selectedFormaEnvio" id="selectedFormaEnvio" value="">
                    <input type="hidden" name="selectedFormaPago" id="selectedFormaPago" value="">

                    <div id="tarjetaFields" style="display: none;" novalidate>
                        <p class="mt-4 texto-seccion">Datos de tarjeta</p>
                        <div class="mb-3">
                            <label for="tarjeta">Número de tarjeta</label>
                            <input type="text" class="form-control" name="tarjeta" id="tarjeta" maxlength="16" pattern="\d{16}" placeholder="xxxxxxxxxxxxxxxx">
                            <?php if (session('errors.tarjeta')): ?>
                                <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.tarjeta'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="vencimiento">Fecha de vencimiento</label>
                            <input type="month" class="form-control" name="vencimiento" id="vencimiento">
                            <?php if (session('errors.vencimiento')): ?>
                                <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.vencimiento'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="cvv">CVV</label>
                            <input type="text" class="form-control" name="cvv" id="cvv" maxlength="4" pattern="\d{3,4}" placeholder="XXX">
                            <?php if (session('errors.cvv')): ?>
                                <div class="mt-1 fw-bold text-danger alert alert-danger"><?= session('errors.cvv'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Compra</button>
                </div>
            </form>
        </div> 
    </div>-->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formaEnvioSelect = document.getElementById('formaEnvio');
    const formaPagoSelect = document.getElementById('formaPago');

    const domicilioFields = document.getElementById('domicilioFields');
    const ciudadInput = document.getElementById('modal_ciudad');
    const provinciaInput = document.getElementById('modal_provincia');
    const domicilioInput = document.getElementById('modal_domicilio');
    const telefonoInput = document.getElementById('modal_telefono');

    const tarjetaFields = document.getElementById('tarjetaFields');
    const tarjetaInput = document.getElementById('tarjeta');
    const vencimientoInput = document.getElementById('vencimiento');
    const cvvInput = document.getElementById('cvv');

    const selectedFormaEnvioInput = document.getElementById('selectedFormaEnvio');
    const selectedFormaPagoInput = document.getElementById('selectedFormaPago');

    function toggleFieldsVisibility() {
        // Asegura que se usan los valores del formulario principal
        const currentFormaEnvio = formaEnvioSelect.value;
        const currentFormaPago = formaPagoSelect.value;

        // Toggle Domicilio, Ciudad, Provincia
        if (currentFormaEnvio === '2') { // 2 = Domicilio
            domicilioFields.style.display = 'block';
            domicilioInput.setAttribute('required', 'required');
            ciudadInput.setAttribute('required', 'required');
            provinciaInput.setAttribute('required', 'required');
        } else {
            domicilioFields.style.display = 'none';
            domicilioInput.removeAttribute('required');
            ciudadInput.removeAttribute('required');
            provinciaInput.removeAttribute('required');
        }

        // Toggle tarjeta
        if (currentFormaPago === '2') { // 2 es 'Tarjeta'
            tarjetaFields.style.display = 'block';
            tarjetaInput.setAttribute('required', 'required');
            vencimientoInput.setAttribute('required', 'required');
            cvvInput.setAttribute('required', 'required');
        } else {
            tarjetaFields.style.display = 'none';
            tarjetaInput.removeAttribute('required');
            vencimientoInput.removeAttribute('required');
            cvvInput.removeAttribute('required');
        }
    }

    // Cuando el modal está a punto de mostrarse, se transfieren los valores seleccionados
    // Los campos internos del modal se actualicen antes de mostrarse.
    const confirmarCompraModal = document.getElementById('confirmarCompraModal');
    confirmarCompraModal.addEventListener('show.bs.modal', function () {
        // Transfiere los valores del formulario principal a los campos ocultos del modal
        selectedFormaEnvioInput.value = formaEnvioSelect.value;
        selectedFormaPagoInput.value = formaPagoSelect.value;

        toggleFieldsVisibility();
    });

    // Si se hacen cambios en los selectores principales para actualizar los campos del modal dinámicamente
    formaEnvioSelect.addEventListener('change', toggleFieldsVisibility);
    formaPagoSelect.addEventListener('change', toggleFieldsVisibility);

    // El modal se abre automáticamente si hay errores
    <?php if (session()->getFlashdata('errors')): ?>
        const modal = new bootstrap.Modal(document.getElementById('confirmarCompraModal'));
        modal.show();
    <?php endif; ?>

    // Llamada inicial para que los campos condicionales se muestren u oculten 
    // correctamente al cargar la página, especialmente si hay valores antiguos 
    // que restaurar en los selectores.
    toggleFieldsVisibility();

});
</script>