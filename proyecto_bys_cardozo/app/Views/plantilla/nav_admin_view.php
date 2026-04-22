<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?></title>
    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/estilos_admin.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Diplomata">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>

  <body>

<nav class="navbar navbar-expand-lg bg-dark bg-opacity-50">
  <div class="container-fluid">
      <div class="d-flex align-items-center me-3">
        <img src="<?= base_url('assets/img/logo.png'); ?>" class="img-fluid" alt="Logo" width="60">
      </div>
    <!-- Botón hamburguesa -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido del navbar -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">

      <!-- Logo -->
      

      <!-- Menú -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('consultas'); ?>">Consultas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('producto'); ?>">Productos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('gestionar_ventas'); ?>">Ventas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('gestionar'); ?>">Gestionar catálogo</a>
        </li>
      </ul>

      <!-- Usuario y logout -->
      <div class="d-flex align-items-center justify-content-end gap-3">
        <?php if (session('login')) : ?>
          <span class="text-white fw-bold"><?= session('apellido'); ?></span>
          <a class="btn" href="<?= base_url('logout'); ?>">
            <i class="fa-solid fa-right-from-bracket"></i>
          </a>
        <?php else : ?>
          <a class="btn" href="<?= base_url('registro'); ?>">
            <i class="fa-solid fa-user"></i>
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</nav>
