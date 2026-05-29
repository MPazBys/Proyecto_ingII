<div class="contenedor-wrapper">
    <p class="titulo-seccion">Administración de Pedidos y Ventas</p>

    <?php if (session()->getFlashdata('mensaje')): ?>
        <div class="alert alert-success fw-bold text-center"><?= session()->getFlashdata('mensaje') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('msj')): ?>
        <div class="alert alert-danger fw-bold text-center"><?= session()->getFlashdata('msj') ?></div>
    <?php endif; ?>

    <!-- SECCIÓN 1: PEDIDOS PENDIENTES (PREPARACIÓN) -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Pedidos Pendientes (Preparación)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($ventasPendientes)): ?>
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="text-center table-secondary">
                            <tr>
                                <th>N° Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Forma de Pago</th>
                                <th>Forma de Envío</th>
                                <th>Detalles</th>
                                <th>Estado / Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ventasPendientes as $row): ?>
                                <tr>
                                    <td class="text-center fw-bold">#<?= $row['idVenta']; ?></td>
                                    <td class="text-center"><?= $row['fecha']; ?></td>
                                    <td><?= esc($row['correoPersona']); ?></td>
                                    <td class="text-end">$<?= number_format($row['total'], 2, ',', '.'); ?></td>
                                    <td class="text-center"><?= esc($row['nombrePago']); ?></td>
                                    <td class="text-center">
                                        <?= ($row['formaEnvio'] == '2') ? '<span class="badge bg-info text-dark"><i class="bi bi-truck"></i> A Domicilio</span>' : '<span class="badge bg-secondary"><i class="bi bi-shop"></i> Retiro en Sucursal</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" onclick="mostrarDetalles(<?= $row['idVenta']; ?>)">
                                            Ver detalles
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['formaEnvio'] == '2'): ?>
                                            <a class="btn btn-sm btn-primary fw-bold" href="<?= base_url('cambiar_estado/'.$row['idVenta'].'/Enviado'); ?>">
                                                <i class="bi bi-send"></i> Marcar como Enviado
                                            </a>
                                        <?php else: ?>
                                            <a class="btn btn-sm btn-success fw-bold" href="<?= base_url('cambiar_estado/'.$row['idVenta'].'/Finalizado'); ?>">
                                                <i class="bi bi-check-circle"></i> Finalizar Compra
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center alert alert-light mb-0">No hay pedidos pendientes de preparación.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: PEDIDOS ENVIADOS (EN DISTRIBUCIÓN) -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-truck"></i> Pedidos Enviados (En Distribución)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($ventasEnviadas)): ?>
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="text-center table-secondary">
                            <tr>
                                <th>N° Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Forma de Pago</th>
                                <th>Forma de Envío</th>
                                <th>Detalles</th>
                                <th>Estado / Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ventasEnviadas as $row): ?>
                                <tr>
                                    <td class="text-center fw-bold">#<?= $row['idVenta']; ?></td>
                                    <td class="text-center"><?= $row['fecha']; ?></td>
                                    <td><?= esc($row['correoPersona']); ?></td>
                                    <td class="text-end">$<?= number_format($row['total'], 2, ',', '.'); ?></td>
                                    <td class="text-center"><?= esc($row['nombrePago']); ?></td>
                                    <td class="text-center">
                                        <?= ($row['formaEnvio'] == '2') ? '<span class="badge bg-info text-dark"><i class="bi bi-truck"></i> A Domicilio</span>' : '<span class="badge bg-secondary"><i class="bi bi-shop"></i> Retiro en Sucursal</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" onclick="mostrarDetalles(<?= $row['idVenta']; ?>)">
                                            Ver detalles
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-warning text-white fw-bold" href="<?= base_url('cambiar_estado/'.$row['idVenta'].'/Finalizado'); ?>">
                                            <i class="bi bi-house-door"></i> Marcar como Entregado
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center alert alert-light mb-0">No hay pedidos en distribución activa.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: PEDIDOS FINALIZADOS (HISTORIAL) -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-check-circle"></i> Pedidos Finalizados (Historial de Éxito)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($ventasFinalizadas)): ?>
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="text-center table-secondary">
                            <tr>
                                <th>N° Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Forma de Pago</th>
                                <th>Forma de Envío</th>
                                <th>Detalles</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ventasFinalizadas as $row): ?>
                                <tr>
                                    <td class="text-center fw-bold">#<?= $row['idVenta']; ?></td>
                                    <td class="text-center"><?= $row['fecha']; ?></td>
                                    <td><?= esc($row['correoPersona']); ?></td>
                                    <td class="text-end">$<?= number_format($row['total'], 2, ',', '.'); ?></td>
                                    <td class="text-center"><?= esc($row['nombrePago']); ?></td>
                                    <td class="text-center">
                                        <?= ($row['formaEnvio'] == '2') ? '<span class="badge bg-info text-dark"><i class="bi bi-truck"></i> A Domicilio</span>' : '<span class="badge bg-secondary"><i class="bi bi-shop"></i> Retiro en Sucursal</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" onclick="mostrarDetalles(<?= $row['idVenta']; ?>)">
                                            Ver detalles
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success py-2 px-3 fw-bold"><i class="bi bi-check-lg"></i> Finalizado</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center alert alert-light mb-0">No hay pedidos finalizados en el historial.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles (compartido por todas las tablas) -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleModalLabel">Detalles de la Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <!-- Se carga dinámicamente con JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function mostrarDetalles(idVenta) {
    fetch("<?= base_url('detalle_venta/') ?>" + idVenta)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detalleModal')).show();
        })
        .catch(error => {
            document.getElementById('detalleContenido').innerHTML = "Error al cargar los detalles.";
            console.error(error);
        });
}
</script>
