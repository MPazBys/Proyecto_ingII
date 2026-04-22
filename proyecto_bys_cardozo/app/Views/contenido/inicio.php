<!--Imagen con el nombre de la libreria-->
<div class="contenedor">
  <img src="<?=('assets/img/nombre-libreria.png'); ?>" class="d-block mx-auto" alt="libreria" width="60%">
</div>

<!--Seccion del carrusel con imagenes-->
<section class="carrusel m-3">
  <div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
    <div class="carousel-inner">
      <div class="carousel-item active position-relative text-center">
        <img src="<?php echo base_url('assets/img/carrusel1.jpg'); ?>" class="d-block mx-auto w-75" alt="carrusel1">
        <div class="texto-superpuesto-izquierda">
          <p>"Hay grandes libros en el <br> mundo y grandes mundos <br> en los libros"<br>
            Gonzalo Andrés Muños
          </p>
        </div>
      </div>
      <div class="carousel-item position-relative text-center">
        <img src="<?php echo base_url('assets/img/carrusel2.jpg'); ?>" class="d-block mx-auto w-75" alt="carrusel2">
        <div class="texto-superpuesto-derecha">
          <p>Compra tus libros de forma <br>rápida y sencilla desde la web <br>elige un medio de pago y el <br>
              tipo de envio que prefieras o <br>retiralo en nuestra sucursal <br>sin costo</p>
        </div>
      </div>
      <div class="carousel-item position-relative text-center">
        <img src="<?php echo base_url('assets/img/carrusel3.jpg'); ?>" class="d-block mx-auto w-75" alt="carrusel3">
        <div class="texto-superpuesto-izquierda">
          <p><strong>23 de abril <br> Día Internacional del Libro</strong> <br>
              "Nunca se termina de aprender <br>a leer. Tal vez como nunca <br>se termina de aprender a vivir" <br>
              Jorge Luis Borges
          </p>
        </div>
      </div>
      <div class="carousel-item position-relative text-center">
        <img src="<?php echo base_url('assets/img/carrusel4.jpg'); ?>" class="d-block mx-auto w-75" alt="carrusel4">
        <div class="texto-superpuesto-derecha">
          <p>"El leer sin pensar nos hace <br>un mente desordenada. El <br>pensar sin leer nos hace <br>desequilibrados"<br>
            Confucio
          </p>
        </div>
      </div>
    </div>

    <!-- Botón anterior -->
    <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>

    <!-- Botón siguiente -->
    <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>
  </div>
</section>


<!-- Sección de Novedades -->
<section class="novedades m-5">
  <p class="titulos text-center fs-2 fw-bold">NOVEDADES</p>
    <div class="d-flex justify-content-center gap-4 flex-wrap">

    
      <?php foreach($libro as $row) { ?>
        <?php if($row['etiquetaLibro'] == 3) { ?>
        <div class="card libro-card" >
          <img src="<?php echo base_url('assets/upload/'.$row['imagenLibro']); ?>" alt="Card image cap" class="class card-img-top">
          <div class="card-body text-center">
              <h6 class="card-title"><?php echo $row['nombreLibro']; ?></h6>
              <p class="card-text text-muted"><?php echo $row['nombreAutor']; ?></p>
              <p class="card-text text-muted"><?php echo "$"; echo $row['precioLibro']; ?></p>
              <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($row['descripcionLibro']) ?>">Leer sinopsis</span>
          </div>
        </div>
        <?php } ?>
      <?php } ?>

    </div>
</section>


  
<!--Seccion de destacados-->
<section class="destacados m-5">
  <p class="titulos text-center">DESTACADOS</p>
    <div class="d-flex justify-content-center gap-4 flex-wrap">

      <?php foreach($libro as $row) { ?>
        <?php if($row['etiquetaLibro'] == 2) { ?>
        <div class="card libro-card" >
          <img src="<?php echo base_url('assets/upload/'.$row['imagenLibro']); ?>" alt="Card image cap" class="class card-img-top">
          <div class="card-body text-center">
              <h6 class="card-title"><?php echo $row['nombreLibro']; ?></h6>
              <p class="card-text text-muted"><?php echo $row['nombreAutor']; ?></p>
              <p class="card-text text-muted"><?php echo "$"; echo $row['precioLibro']; ?></p>
              <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($row['descripcionLibro']) ?>">Leer sinopsis</span>
          </div>
        </div>
        <?php } ?>
      <?php } ?>

    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))
    });
</script>