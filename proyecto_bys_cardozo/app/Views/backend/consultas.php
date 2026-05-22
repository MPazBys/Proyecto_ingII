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
                        <th>Motivo</th>
                        <th>Consulta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultas as $consulta): ?>
                        <tr>
                            <td><?= esc($consulta['nombreApellido']) ?></td>
                            <td><?= esc($consulta['correo']) ?></td>
                            <td><?= esc($consulta['asunto']) ?></td>
                            <td><?= esc($consulta['mensaje']) ?></td>
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
                                       target="_blank" 
                                       class="btn btn-sm btn-primary" 
                                       onclick="setTimeout(() => { window.location.reload(); }, 1000);">
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
<?= $this->endSection() ?>