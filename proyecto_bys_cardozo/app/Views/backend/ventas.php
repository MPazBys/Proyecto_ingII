<div class="contenedor-wrapper">
    <p class="titulo-seccion">Listado de Ventas</p>

    <div class="container mt-5">
    <div class="alert alert-info shadow-sm d-flex align-items-center rounded-pill p-3" role="alert">
        <i class="fa-solid fa-person-digging fs-3 me-3 text-primary"></i>
        <div>
            <h4 class="alert-heading mb-1 fw-bold text-primary">¡Sección en proceso!</h4>
            <p class="mb-0 text-muted">Estamos trabajando para traerte esta funcionalidad muy pronto. ¡Gracias por tu paciencia!</p>
        </div>
    </div>
</div>
    <!-- <div class="table-responsive">
        <?php if (!empty($venta)): ?>
            <table id="mytable" class="table table-bordered table-striped table-hover">
                <thead class="text-center table-dark">
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Forma de pago</th>
                    <th>Detalles</th>
                    <th>Estado</th>
                    <th>Cambiar estado</th>
                </thead>

                <tbody>
                    <?php foreach($venta as $row): ?>
                        <tr>
                            <td><?= $row['fecha']; ?></td>
                            <td><?= $row['correoPersona']; ?></td>
                            <td>$<?= $row['total']; ?></td>
                            <td><?= $row['nombrePago']; ?></td>

                             Botón de detalles con onclick 
                            <td class="text-center">
                                <button class="btn btn-sm btn-info"
                                    onclick="mostrarDetalles(<?= $row['idVenta']; ?>)">
                                    Ver detalles
                                </button>
                            </td>

                            <td><?= $row['estado']; ?></td>

                            <?php if ($row['estado'] == 'Pendiente'): ?>
                                <td><a class="btn btn-danger" href="<?= base_url('finalizado/'.$row['idVenta']); ?>">Cambiar Estado</a></td>
                            <?php else: ?>
                                <td>Pedido Finalizado</td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

              Modal fuera del foreach pero dentro del if 
            <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="detalleModalLabel">Detalles de la venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body" id="detalleContenido">
                    Se carga dinámicamente con JavaScript 
                  </div>
                </div>
              </div>
            </div>

        <?php else: ?>
            <div class="text-center">
                <p class="alert alert-danger fw-bold">No hay ventas registradas.</p>
            </div>
        <?php endif; ?>
    </div> -->
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
