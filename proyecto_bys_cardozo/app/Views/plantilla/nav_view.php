<nav class="navbar navbar-expand-lg bg-body-tertiary">
 <div class="container-fluid">
    <!--boton que desplega el menu en pantallas pequeñas-->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!--menu-->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="<?php echo base_url(''); ?>">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= ('nosotros'); ?>">Nosotros</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= ('contactos'); ?>">Contacto</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= ('comercializacion'); ?>">Informacion</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= ('terminos_usos'); ?>">Terminos y usos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= ('productos'); ?>">Catalogo</a>
        </li>
      </ul>
    </div>
  </div>
</nav>