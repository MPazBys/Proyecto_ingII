<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?></title>
    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/estilos.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Diplomata">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>

  <body>
    <header>
      <nav class="navbar navbar-expand-lg">
        <div class="container-fluid d-block">
          <div class="conteiner mt-2">
            <div class="row">

              <!--formulario para buscar-->
              <div class="col-4">
                <?= form_open('buscar', ['method' => 'get', 'class' => 'd-flex', 'role' => 'search']) ?>
                  <?= form_input([
                    'name'        => 'busqueda',
                    'type'        => 'search',
                    'class'       => 'form-control me-2',
                    'placeholder' => '¿Qué estás buscando?',
                    'aria-label'  => 'Buscar'
                  ]) ?>
                  
                  <button class="btn btn-buscar btn-outline-success" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                  </button>
                <?= form_close() ?>
              </div>

              <!--logo de la empresa-->
              <div class="col-4 text-center">
                <img src="<?php echo base_url('assets/img/logo.png'); ?>" class="img-fluid d-block mx-auto" alt="Logo" width="60">
              </div>

              <div class="col-4 d-flex justify-content-end align-items-center gap-2">
              <?php if(session('login')) { ?>
              
                  <span class="text-white fw-bold"><?= session('apellido'); ?></span>
                  <a class="btn" href="<?= ('ver_carrito'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Carrito">
                    <i class="fa-solid fa-cart-shopping"></i>
                  </a>

                  <a class="btn" href="<?= base_url('logout'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Cerrar sesión">
                    <i class="fa-solid fa-right-from-bracket"></i>
                  </a>

                <?php } else { ?>
                  <a class="btn" href="<?= ('login'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Iniciar sesión">
                    <i class="fa-solid fa-lock"></i>
                  </a>

                  <a class="btn" href="<?= ('registro'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrarte">
                    <i class="fa-solid fa-user" ></i>
                  </a>
                <?php } ?> 
            </div>
            </div>
          </div>
        </div> 
      </nav>
    </header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))
    });
</script>