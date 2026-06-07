<?php $cart = \Config\Services::cart(); ?>

<div class="contenedor-wrapper">
    <p class="titulo-seccion">Carrito de compras</p>

    <a href="<?= base_url('productos') ?>" class="btn btn-success" role="button">Continuar comprando</a>

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
                <select class="form-select mt-2 <?= session('errors.selectedFormaEnvio') ? 'is-invalid' : '' ?>" id="formaEnvio" name="formaEnvio" required>
                    <option value="" <?= set_select('selectedFormaEnvio', ''); ?>>Seleccione forma de envío</option> 
                    <option value="1" <?= set_select('selectedFormaEnvio', '1', (old('selectedFormaEnvio') ?? '') == '1'); ?>>Retiro en sucursal</option> 
                    <option value="2" <?= set_select('selectedFormaEnvio', '2', (old('selectedFormaEnvio') ?? '') == '2'); ?>>Envío a domicilio</option> 
                </select>
                <?php if (session('errors.selectedFormaEnvio')): ?>
                    <div class="invalid-feedback"><?= session('errors.selectedFormaEnvio') ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="formaPago">Forma de Pago:</label>
                <select class="form-select mt-2 <?= session('errors.selectedFormaPago') ? 'is-invalid' : '' ?>" id="formaPago" name="formaPago" required>
                    <option value="" <?= set_select('selectedFormaPago', ''); ?>>Seleccione forma de pago</option> 
                    <?php foreach($formasPago as $fp): ?>
                        <option value="<?= esc($fp['idPago']); ?>" <?= set_select('selectedFormaPago', $fp['idPago'], (old('selectedFormaPago') ?? '') == $fp['idPago']); ?>>
                            <?= esc($fp['nombrePago']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.selectedFormaPago')): ?>
                    <div class="invalid-feedback"><?= session('errors.selectedFormaPago') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <button type="button" id="btnOrdenarCompra" class="btn btn-success mt-4">Ordenar Compra</button>
    <?php endif; ?>
</div>

<div class="modal fade" id="confirmarCompraModal" tabindex="-1" aria-labelledby="confirmarCompraModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarCompraModalLabel">Confirmar Compra y Datos de Envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open('procesar_finalizar_compra', ['id' => 'finalizarCompraForm', 'novalidate' => 'novalidate']) ?>
                <div class="modal-body">

                    <p class="texto-seccion text-primary border-bottom pb-2">Información de contacto</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_dni" class="form-label">DNI <span class="text-danger">*</span></label>
                            <input type="text" name="dni" id="modal_dni" class="form-control <?= session('errors.dni') ? 'is-invalid' : '' ?>" placeholder="DNI sin puntos" value="<?= old('dni', $persona['dni'] ?? '') ?>" <?= !empty($persona['dni']) ? 'readonly' : '' ?> required>
                            <?php if (session('errors.dni')): ?>
                                <div class="invalid-feedback"><?= session('errors.dni') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" name="telefono" id="modal_telefono" class="form-control <?= session('errors.telefono') ? 'is-invalid' : '' ?>" placeholder="Ej: 3794123456" value="<?= old('telefono', $persona['telefono'] ?? '') ?>" required>
                            <?php if (session('errors.telefono')): ?>
                                <div class="invalid-feedback"><?= session('errors.telefono') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Campos ocultos para enviar las formas de envío y pago seleccionadas -->
                    <input type="hidden" name="selectedFormaEnvio" id="selectedFormaEnvio" value="">
                    <input type="hidden" name="selectedFormaPago" id="selectedFormaPago" value="">

                    <!-- Recuadro para Dirección (se muestra sólo si es envío a domicilio) -->
                    <div id="domicilioFields" style="display: none;">
                        <p class="mt-4 texto-seccion text-primary border-bottom pb-2">Información de domicilio</p>

                        <?php if (!empty($direccion)): ?>
                            <div class="mb-3 text-end">
                                <button type="button" id="btnCambiarDireccion" class="btn btn-outline-primary btn-sm">
                                    Cambiar dirección
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="modal_calle" class="form-label">Calle <span class="text-danger">*</span></label>
                                <input type="text" name="calle" id="modal_calle" class="form-control <?= session('errors.calle') ? 'is-invalid' : '' ?>" placeholder="Nombre de calle" value="<?= old('calle', $direccion['calle'] ?? '') ?>" <?= !empty($direccion) ? 'readonly' : '' ?> required>
                                <?php if (session('errors.calle')): ?>
                                    <div class="invalid-feedback"><?= session('errors.calle') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modal_altura" class="form-label">Altura <span class="text-danger">*</span></label>
                                <input type="text" name="altura" id="modal_altura" class="form-control <?= session('errors.altura') ? 'is-invalid' : '' ?>" placeholder="Ej: 123" value="<?= old('altura', $direccion['altura'] ?? '') ?>" <?= !empty($direccion) ? 'readonly' : '' ?> required>
                                <?php if (session('errors.altura')): ?>
                                    <div class="invalid-feedback"><?= session('errors.altura') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modal_pisoDepto" class="form-label">Piso / Depto</label>
                                <input type="text" name="pisoDepto" id="modal_pisoDepto" class="form-control <?= session('errors.pisoDepto') ? 'is-invalid' : '' ?>" placeholder="Ej: 1A (Opcional)" value="<?= old('pisoDepto', $direccion['pisoDepto'] ?? '') ?>" <?= !empty($direccion) ? 'readonly' : '' ?>>
                                <?php if (session('errors.pisoDepto')): ?>
                                    <div class="invalid-feedback"><?= session('errors.pisoDepto') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_provincia" class="form-label">Provincia <span class="text-danger">*</span></label>
                                <select name="provincia" id="modal_provincia" class="form-select <?= session('errors.provincia') ? 'is-invalid' : '' ?>" <?= !empty($direccion) ? 'disabled' : '' ?> required>
                                    <option value="">Seleccione su provincia</option>
                                    <?php foreach ($provincias as $prov): ?>
                                        <option value="<?= esc($prov['idProvincia']) ?>" <?= set_select('provincia', $prov['idProvincia'], ($idProvinciaCliente ?? '') == $prov['idProvincia']) ?>>
                                            <?= esc($prov['nombreProvincia']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (session('errors.provincia')): ?>
                                    <div class="invalid-feedback"><?= session('errors.provincia') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_ciudad" class="form-label">Ciudad / Localidad <span class="text-danger">*</span></label>
                            <select name="idLocalidad" id="modal_ciudad" class="form-select <?= session('errors.idLocalidad') ? 'is-invalid' : '' ?>" <?= !empty($direccion) ? 'disabled' : '' ?> required>
                                <option value="">Seleccione su ciudad</option>
                                <?php foreach ($localidades as $loc): ?>
                                    <option value="<?= esc($loc['idLocalidad']) ?>" data-provincia="<?= esc($loc['idProvincia']) ?>" <?= set_select('idLocalidad', $loc['idLocalidad'], ($idLocalidadCliente ?? '') == $loc['idLocalidad']) ?>>
                                        <?= esc($loc['nombreLocalidad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.idLocalidad')): ?>
                                <div class="invalid-feedback"><?= session('errors.idLocalidad') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="modal_consideraciones" class="form-label">Consideraciones / Indicaciones para el delivery</label>
                            <textarea name="consideraciones" id="modal_consideraciones" class="form-control <?= session('errors.consideraciones') ? 'is-invalid' : '' ?>" placeholder="Ej: Casa de rejas negras, timbre no funciona (Opcional)" <?= !empty($direccion) ? 'readonly' : '' ?>><?= old('consideraciones', $direccion['consideraciones'] ?? '') ?></textarea>
                            <?php if (session('errors.consideraciones')): ?>
                                <div class="invalid-feedback"><?= session('errors.consideraciones') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datos de tarjeta (se muestra sólo si es pago con tarjeta) -->
                    <div id="tarjetaFields" style="display: none;">
                        <p class="mt-4 texto-seccion text-primary border-bottom pb-2">Datos de tarjeta</p>
                        <div class="mb-3">
                            <label for="tarjeta" class="form-label">Número de tarjeta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.tarjeta') ? 'is-invalid' : '' ?>" name="tarjeta" id="tarjeta" maxlength="16" placeholder="16 dígitos sin espacios" value="<?= old('tarjeta') ?>">
                            <?php if (session('errors.tarjeta')): ?>
                                <div class="invalid-feedback"><?= session('errors.tarjeta') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vencimiento" class="form-label">Fecha de vencimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= session('errors.vencimiento') ? 'is-invalid' : '' ?>" name="vencimiento" id="vencimiento" placeholder="MM/AA" maxlength="5" value="<?= old('vencimiento') ?>" required>
                                <?php if (session('errors.vencimiento')): ?>
                                    <div class="invalid-feedback"><?= session('errors.vencimiento') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= session('errors.cvv') ? 'is-invalid' : '' ?>" name="cvv" id="cvv" maxlength="4" placeholder="3 o 4 dígitos" value="<?= old('cvv') ?>">
                                <?php if (session('errors.cvv')): ?>
                                    <div class="invalid-feedback"><?= session('errors.cvv') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Compra</button>
                </div>
            <?= form_close() ?>
        </div> 
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
    const vencimientoInput = document.getElementById('vencimiento');

    if (vencimientoInput) {
        // 1. MÁSCARA EN TIEMPO REAL (Formatea automáticamente a MM/AA)
        vencimientoInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Remueve cualquier letra o símbolo
            
            if (value.length > 2) {
                e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                e.target.value = value;
            }

            // Si el usuario vuelve a escribir, limpiamos los estados de error visuales
            e.target.classList.remove('is-invalid');
            const feedback = e.target.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.style.display = 'none';
            }
        });

        // 2. CONTROL DE EXPIRACIÓN MATEMÁTICA (Se ejecuta cuando el usuario cambia de campo)
        vencimientoInput.addEventListener('change', function (e) {
            const valor = e.target.value;
            
            // Verificamos que se haya completado el patrón MM/AA (5 caracteres)
            if (valor.length === 5 && valor.includes('/')) {
                const partes = valor.split('/');
                const mesIngresado = parseInt(partes[0], 10);
                const anioIngresado = parseInt('20' + partes[1], 10); 

                // Validar que el mes exista (01 a 12)
                if (mesIngresado < 1 || mesIngresado > 12) {
                    marcarErrorTarjeta(e.target, 'Mes inválido. Debe ingresar un valor entre 01 y 12.');
                    return;
                }

                // Captura dinámica de la fecha actual del sistema
                const fechaActual = new Date();
                const mesActual = fechaActual.getMonth() + 1; 
                const anioActual = fechaActual.getFullYear(); 

                // EVALUACIÓN DE VENCIMIENTO:
                // Si el año es menor al actual, o si es el mismo año pero el mes ya pasó, se rechaza.
                if (anioIngresado < anioActual || (anioIngresado === anioActual && mesIngresado < mesActual)) {
                    marcarErrorTarjeta(e.target, 'La tarjeta ingresada ya no se encuentra vigente o se encuentra vencida.');
                }
            }
        });
    }

    // Función auxiliar para unificar el comportamiento estético de los errores
    function marcarErrorTarjeta(inputElement, mensajeTexto) {
        inputElement.classList.add('is-invalid'); 
        inputElement.value = ''; 

        // Dispara la alerta bonita de SweetAlert 
        Swal.fire({
            title: 'Tarjeta Inválida',
            icon: 'error',
            text: mensajeTexto,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#198754'
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formaEnvioSelect = document.getElementById('formaEnvio');
    const formaPagoSelect = document.getElementById('formaPago');
    const btnOrdenarCompra = document.getElementById('btnOrdenarCompra');

    const domicilioFields = document.getElementById('domicilioFields');
    const ciudadInput = document.getElementById('modal_ciudad');
    const provinciaInput = document.getElementById('modal_provincia');
    const calleInput = document.getElementById('modal_calle');
    const alturaInput = document.getElementById('modal_altura');
    const pisoDeptoInput = document.getElementById('modal_pisoDepto');
    const consideracionesInput = document.getElementById('modal_consideraciones');
    const btnCambiarDireccion = document.getElementById('btnCambiarDireccion');

    const tarjetaFields = document.getElementById('tarjetaFields');
    const tarjetaInput = document.getElementById('tarjeta');
    const vencimientoInput = document.getElementById('vencimiento');
    const cvvInput = document.getElementById('cvv');

    const selectedFormaEnvioInput = document.getElementById('selectedFormaEnvio');
    const selectedFormaPagoInput = document.getElementById('selectedFormaPago');

    // Guardar una copia de todas las opciones de localidades al inicio para poder filtrarlas
    const allLocalidades = [];
    Array.from(ciudadInput.options).forEach(function(option) {
        if (option.value !== '') {
            allLocalidades.push({
                value: option.value,
                text: option.textContent.trim(),
                provinciaId: option.getAttribute('data-provincia')
            });
        }
    });

    function filterCiudades() {
        const selectedProvId = provinciaInput.value;

        // Guardamos el valor seleccionado actual de la ciudad para intentar conservarlo
        const currentCiudadValue = ciudadInput.value;

        // Limpiamos las opciones del select de ciudad
        ciudadInput.innerHTML = '';

        // Añadimos la opción por defecto
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Seleccione su ciudad';
        ciudadInput.appendChild(defaultOption);

        // Si no hay provincia seleccionada, deshabilitar el dropdown de ciudades
        if (!selectedProvId) {
            ciudadInput.disabled = true;
            return;
        }

        // Si la provincia está deshabilitada (modo lectura), mantenemos la ciudad deshabilitada
        ciudadInput.disabled = provinciaInput.hasAttribute('disabled');

        // Filtramos y añadimos las localidades correspondientes
        let optionSelected = false;
        allLocalidades.forEach(function(loc) {
            if (loc.provinciaId === selectedProvId) {
                const opt = document.createElement('option');
                opt.value = loc.value;
                opt.textContent = loc.text;
                opt.setAttribute('data-provincia', loc.provinciaId);
                // Si coincide con el valor seleccionado anteriormente, lo marcamos como seleccionado
                if (loc.value === currentCiudadValue || loc.value === '<?= $idLocalidadCliente ?? '' ?>') {
                    opt.selected = true;
                    optionSelected = true;
                }
                ciudadInput.appendChild(opt);
            }
        });

        if (!optionSelected) {
            const initialLocalidad = '<?= $idLocalidadCliente ?? '' ?>';
            const hasInitial = allLocalidades.some(l => l.value === initialLocalidad && l.provinciaId === selectedProvId);
            if (hasInitial) {
                ciudadInput.value = initialLocalidad;
            } else {
                ciudadInput.value = '';
            }
        }
    }

    function toggleFieldsVisibility() {
        // Asegura que se usan los valores del formulario principal
        const currentFormaEnvio = formaEnvioSelect.value;
        const currentFormaPago = formaPagoSelect.value;

        // Toggle Domicilio, Ciudad, Provincia
        if (currentFormaEnvio === '2') { // 2 = Domicilio
            domicilioFields.style.display = 'block';
            calleInput.setAttribute('required', 'required');
            alturaInput.setAttribute('required', 'required');
            ciudadInput.setAttribute('required', 'required');
            provinciaInput.setAttribute('required', 'required');
        } else {
            domicilioFields.style.display = 'none';
            calleInput.removeAttribute('required');
            alturaInput.removeAttribute('required');
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

    // Registrar escuchadores de eventos
    provinciaInput.addEventListener('change', filterCiudades);

    // Al hacer clic en el botón Ordenar Compra del formulario del carrito
    btnOrdenarCompra.addEventListener('click', function() {
        // Limpiamos estilos de error previos
        formaEnvioSelect.classList.remove('is-invalid');
        formaPagoSelect.classList.remove('is-invalid');

        const env = formaEnvioSelect.value;
        const pag = formaPagoSelect.value;

        let hasError = false;
        if (!env) {
            formaEnvioSelect.classList.add('is-invalid');
            hasError = true;
        }
        if (!pag) {
            formaPagoSelect.classList.add('is-invalid');
            hasError = true;
        }

        if (hasError) {
            Swal.fire({
                title: 'Atención',
                icon: 'warning',
                text: 'Debe seleccionar obligatoriamente una Forma de Envío y una Forma de Pago antes de ordenar la compra.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Si están seleccionados, transferimos sus valores a los campos hidden
        selectedFormaEnvioInput.value = env;
        selectedFormaPagoInput.value = pag;

        // Actualizamos los campos condicionales y localidades del modal
        toggleFieldsVisibility();
        filterCiudades();

        // Abrimos el modal programáticamente
        const modal = new bootstrap.Modal(document.getElementById('confirmarCompraModal'));
        modal.show();
    });

    // Cambiar dirección (habilitar campos)
    if (btnCambiarDireccion) {
        btnCambiarDireccion.addEventListener('click', function() {
            calleInput.removeAttribute('readonly');
            alturaInput.removeAttribute('readonly');
            pisoDeptoInput.removeAttribute('readonly');
            consideracionesInput.removeAttribute('readonly');
            
            provinciaInput.removeAttribute('disabled');
            ciudadInput.removeAttribute('disabled');
            
            btnCambiarDireccion.style.display = 'none';
        });
    }

    // Habilitar campos selectores antes de enviar el formulario para que viajen por POST
    const finalizarCompraForm = document.getElementById('finalizarCompraForm');
    if (finalizarCompraForm) {
        finalizarCompraForm.addEventListener('submit', function() {
            provinciaInput.removeAttribute('disabled');
            ciudadInput.removeAttribute('disabled');
        });
    }

    // Si hay errores de validación devueltos por el backend, abrimos el modal
    <?php if (session('errors')): ?>
        formaEnvioSelect.value = "<?= old('selectedFormaEnvio') ?>";
        formaPagoSelect.value = "<?= old('selectedFormaPago') ?>";
        selectedFormaEnvioInput.value = "<?= old('selectedFormaEnvio') ?>";
        selectedFormaPagoInput.value = "<?= old('selectedFormaPago') ?>";
        
        toggleFieldsVisibility();
        filterCiudades();

        const modal = new bootstrap.Modal(document.getElementById('confirmarCompraModal'));
        modal.show();
    <?php endif; ?>

    // Sincronizar en tiempo real si el usuario cambia los selects principales antes de presionar el botón
    formaEnvioSelect.addEventListener('change', function() {
        formaEnvioSelect.classList.remove('is-invalid');
    });
    formaPagoSelect.addEventListener('change', function() {
        formaPagoSelect.classList.remove('is-invalid');
    });

    // Inicializaciones
    toggleFieldsVisibility();
    filterCiudades();
});
</script>