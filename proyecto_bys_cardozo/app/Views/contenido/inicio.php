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
  <div class="carousel-libros-wrapper">
    <button class="btn-scroll prev-scroll" onclick="scrollLibros(this, -1)">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <div class="carousel-libros-container d-flex gap-4">
      <?php if (isset($libro) && is_array($libro)): ?>
        <?php foreach($libro as $row) { ?>
          <?php if($row['idEtiqueta'] == 3) { ?>
            <?= view('contenido/tarjeta_libro', ['row' => $row]) ?>
          <?php } ?>
        <?php } ?>
      <?php endif; ?>
    </div>
    <button class="btn-scroll next-scroll" onclick="scrollLibros(this, 1)">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
</section>


  
<!--Seccion de destacados-->
<section class="destacados m-5">
  <p class="titulos text-center">DESTACADOS</p>
  <div class="carousel-libros-wrapper">
    <button class="btn-scroll prev-scroll" onclick="scrollLibros(this, -1)">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <div class="carousel-libros-container d-flex gap-4">
      <?php if (isset($libro) && is_array($libro)): ?>
        <?php foreach($libro as $row) { ?>
          <?php if($row['idEtiqueta'] == 2) { ?>
            <?= view('contenido/tarjeta_libro', ['row' => $row]) ?>
          <?php } ?>
        <?php } ?>
      <?php endif; ?>
    </div>
    <button class="btn-scroll next-scroll" onclick="scrollLibros(this, 1)">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))
    });

    function scrollLibros(btn, direction) {
        const wrapper = btn.closest('.carousel-libros-wrapper');
        const container = wrapper.querySelector('.carousel-libros-container');
        
        const cardWidth = 190 + 24; // 190px de tarjeta + 24px de gap (margin/padding)
        const scrollAmount = cardWidth * 2;
        
        const maxScroll = container.scrollWidth - container.clientWidth;
        
        const now = Date.now();
        const lastClick = parseInt(container.dataset.lastClickTime || '0');
        let target = parseFloat(container.dataset.scrollTarget);
        
        // Si no hay un objetivo previo o si el usuario hizo scroll manual
        if (isNaN(target) || (now - lastClick > 600 && Math.abs(container.scrollLeft - target) > 15)) {
            target = container.scrollLeft;
        }
        
        container.dataset.lastClickTime = now;
        
        // k es la cantidad de tarjetas que movemos (máximo la mitad del total)
        const k = Math.min(2, Math.floor(container.children.length / 2));
        
        if (direction === 1) {
            // Si ya estamos en el final o muy cerca, movemos elementos del inicio al final
            if (target >= maxScroll - 15) {
                if (k > 0) {
                    const shiftWidth = container.children[k].offsetLeft - container.children[0].offsetLeft;
                    
                    // Mover primeros k elementos al final
                    for (let i = 0; i < k; i++) {
                        container.appendChild(container.children[0]);
                    }
                    
                    // Ajustar instantáneamente scrollLeft y target para compensar el cambio de orden del DOM
                    target -= shiftWidth;
                    
                    const originalBehavior = container.style.scrollBehavior;
                    container.style.scrollBehavior = 'auto';
                    container.scrollLeft = target;
                    container.offsetHeight; // Forzar reflow
                    container.style.scrollBehavior = originalBehavior;
                }
            }
            
            // Calcular el nuevo destino hacia la derecha
            target = Math.min(target + scrollAmount, container.scrollWidth - container.clientWidth);
            
        } else if (direction === -1) {
            // Si ya estamos en el inicio o muy cerca, movemos elementos del final al inicio
            if (target <= 15) {
                if (k > 0) {
                    const n = container.children.length;
                    const shiftWidth = container.scrollWidth - container.children[n - k].offsetLeft;
                    
                    // Mover los últimos k elementos al inicio
                    for (let i = 0; i < k; i++) {
                        container.insertBefore(container.children[container.children.length - 1], container.children[0]);
                    }
                    
                    // Ajustar instantáneamente scrollLeft y target para compensar el cambio de orden del DOM
                    target += shiftWidth;
                    
                    const originalBehavior = container.style.scrollBehavior;
                    container.style.scrollBehavior = 'auto';
                    container.scrollLeft = target;
                    container.offsetHeight; // Forzar reflow
                    container.style.scrollBehavior = originalBehavior;
                }
            }
            
            // Calcular el nuevo destino hacia la izquierda
            target = Math.max(target - scrollAmount, 0);
        }
        
        container.dataset.scrollTarget = target;
        
        container.scrollTo({
            left: target,
            behavior: 'smooth'
        });
    }
</script>