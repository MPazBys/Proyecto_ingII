<?= view('plantilla/nav_admin_view', ['titulo' => $titulo ?? null]) ?>

<?= $this->renderSection('contenido') ?>

<?= view('plantilla/footer_admin_view') ?>
