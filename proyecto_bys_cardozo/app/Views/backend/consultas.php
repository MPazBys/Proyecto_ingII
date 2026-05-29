<?= $this->extend('plantilla/layout_admin') ?>

<?= $this->section('contenido') ?>
<div class="container my-5">

    <h1 class="titulo-seccion text-center mb-4">Consultas</h1>
    
        <?php if (session()->getFlashdata('mensaje')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('mensaje'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($consultas) && is_array($consultas)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre y Apellido</th>
                        <th>Email</th>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th>Consulta</th>
                        <th>Respondido por</th>
                        <th>Respuesta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultas as $consulta): ?>
                        <tr>
                            <td><?= esc($consulta['nombreApellido']) ?></td>
                            <td><?= esc($consulta['correo']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($consulta['created_at'])) ?></td>
                            <td><?= esc($consulta['asunto']) ?></td>
                            <td>
                                <span class="btn btn-sm btn-outline-secondary" 
                                      data-bs-toggle="tooltip" 
                                      data-bs-placement="top" 
                                      title="<?= esc($consulta['mensaje']) ?>">
                                    <i class="fa-solid fa-eye me-1"></i> Ver consulta
                                </span>
                            </td>
                            <td><?= !empty($consulta['adminNombreApellido']) ? esc($consulta['adminNombreApellido']) : '-' ?></td>
                            <td>
                                <?php if (!empty($consulta['respuestaText'])): ?>
                                    <span class="btn btn-sm btn-outline-info" 
                                          data-bs-toggle="tooltip" 
                                          data-bs-placement="top" 
                                          title="<?= esc($consulta['respuestaText']) ?>">
                                        <i class="fa-solid fa-envelope-open me-1"></i> Ver respuesta
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($consulta['respondido']): ?>
                                    <span class="badge bg-success">Respondido</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($consulta['respondido']): ?>
                                    <span class="text-muted"><i class="fa-solid fa-check-double text-success me-1"></i> Resuelto</span>
                                <?php else: ?>
                                    <a href="<?= base_url('responder/' . $consulta['idConsulta']) ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-reply me-1"></i> Responder
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No hay consultas registradas.</div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
<?= $this->endSection() ?>