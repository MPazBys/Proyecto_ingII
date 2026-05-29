<?= $this->extend('plantilla/layout_admin') ?>

<?= $this->section('contenido') ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <a href="<?= base_url('consultas') ?>" class="btn btn-outline-secondary mb-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver a Consultas
            </a>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <?= session()->getFlashdata('error'); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0">Detalles de la Consulta Original</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong class="text-muted d-block small">Cliente</strong>
                        <span class="fs-5 fw-bold"><?= esc($consulta['nombreApellido']) ?></span>
                        <span class="text-muted">(&lt;<?= esc($consulta['correo']) ?>&gt;)</span>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted d-block small">Asunto / Motivo</strong>
                        <span class="fs-5 fw-semibold"><?= esc($consulta['asunto']) ?></span>
                    </div>
                    <div class="mb-0">
                        <strong class="text-muted d-block small">Mensaje</strong>
                        <p class="bg-light p-3 rounded border border-light-subtle text-dark mb-0" style="white-space: pre-wrap;"><?= esc($consulta['mensaje']) ?></p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0">Enviar Respuesta</h5>
                </div>
                <div class="card-body">
                    <?= form_open('procesar_respuesta') ?>
                        <input type="hidden" name="idConsulta" value="<?= esc($consulta['idConsulta']) ?>">

                        <div class="mb-4">
                            <label for="respuesta" class="form-label fw-semibold">Tu contestación:</label>
                            
                            <textarea name="respuesta" id="respuesta" rows="6" class="form-control <?= session('validation.respuesta') ? 'is-invalid' : '' ?>" placeholder="Escribe aquí la respuesta para el cliente..."><?= old('respuesta') ?></textarea>
                            
                            <?php if (session('validation.respuesta')): ?>
                                <div class="invalid-feedback d-block font-weight-bold mt-2">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i> <?= session('validation.respuesta') ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-text text-muted">Esta respuesta se guardará en el sistema y se le enviará un correo electrónico de notificación al cliente.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-paper-plane me-1"></i> Enviar Respuesta
                            </button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>