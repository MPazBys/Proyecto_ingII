<?= view('plantilla/header_view', ['titulo' => $titulo ?? null]) ?>
<?= view('plantilla/nav_view') ?>

<?= $this->renderSection('contenido') ?>

<?= view('plantilla/footer_view') ?>
