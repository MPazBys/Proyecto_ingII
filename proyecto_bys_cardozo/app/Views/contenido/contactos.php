<div class="contenedor-wrapper"> 
	<p class="titulo-contacto text-wrap text-break">Información de Contacto</p>

	<!--lista con la informacion de la empresa-->
	<ul> 
		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Nombre de la empresa:</div>
				<p class="info-contacto">
					Libros M&P
				</p>
			</div>
		</li>

		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Razón Social:</div>
				<p class="info-contacto">
						M&P Editorial S.A.
				</p>
			</div>
		</li>

		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Domicilio Legal:</div>
				<p class="info-contacto">
						<i class="fa-solid fa-location-dot" style="color: #ff0000;"></i> Av. Libertad 1234, Piso 2, Oficina 5 
				<br>
					Ciudad de Buenos Aires, Argentina
				</p>
			</div>
		</li>

		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Teléfonos de Contacto:</div>
				<p class="info-contacto">
					📞 +54 11 4567-8901
				<br>
					📞 +54 11 4567-8902
				</p>

			</div>
		</li>

		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Correo Electrónico:</div>
				<p class="info-contacto">
						<i class="fa-regular fa-envelope"></i> contacto_librosmyp@gmail.com
				</p>
			</div>
		</li>

		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Horario de Atención:</div>
				<p class="info-contacto">
					🕘 Lunes a Viernes de 9:00 a 18:00 hs
						<br>
					🕘 Sábados de 9:00 a 13:00 hs
				</p>
			</div>
		</li>

		<li class="list-group-item d-flex justify-content-start align-items-start border-0">
			<div class="ms-1 me-auto">
				<div class="info-contacto fw-bold">Redes Sociales:</div>
				<p class="info-contacto">
						<i class="fa-brands fa-square-facebook"></i> Facebook: /LibrosMyP
						<br>
						<i class="fa-brands fa-instagram"></i> Instagram: @libros.myp
						<br>
						<i class="fa-brands fa-x-twitter"></i> Twitter: @Libros_MyP
				</p>
			</div>
		</li>
	</ul>

	<!--formulario para consultas y mapa-->
	<div class="container my-4">
		<h1 class= "titulo-consulta">Contacto</h1>
		<p class="texto-seccion">¿Tenés alguna consulta? Completá el siguiente formulario y nuestro equipo se pondrá en contacto con vos a la brevedad.</p>

		<div class="row">
			<div class="col-md-6 mx-auto border border-dark text-center mt-5">

			<?php echo form_open('consulta') ?>
			<div class="mb-3">
				<label for="nombreYapellido" class="form-label fw-bold">Nombre y apellido</label>
				<?php echo form_input([
					'name'        => 'nombreYapellido',
					'id'          => 'nombreYapellido',
					'type'        => 'text',
					'class'       => 'form-control',
					'placeholder' => 'Ingrese su nombre y apellido',
					'value'       => set_value('nombreYapellido')
				]); ?>

				<?php if(! empty($validation['nombreYapellido'])): ?>
				<div class="mt-1 fw-bold text-danger alert alert-danger">
					<?= $validation['nombreYapellido']; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="mb-3">
				<label for="email" class="form-label  fw-bold">Correo Electrónico</label>
				<?php echo form_input([
					'name'        => 'correo',
					'id'          => 'correo',
					'type'        => 'email',
					'class'       => 'form-control',
					'placeholder' => 'juanperez@mail.com"',
					'value'       => set_value('correo')
				]); ?>

				<?php if(! empty($validation['correo'])): ?>
				<div class="mt-1 fw-bold text-danger alert alert-danger">
					<?= $validation['correo']; ?>
				</div>
				<?php endif; ?>
			</div>
			<div class="mb-3">
					<label for="motivo" class="form-label fw-bold">Motivo</label>
				<?php echo form_input([
					'name'        => 'motivo',
					'id'          => 'motivo',
					'class'       => 'form-control',
					'placeholder' => 'Ingrese el motivo de la consulta',
					'value'       => set_value('motivo')
				]); ?>

				<?php if(! empty($validation['motivo'])): ?>
				<div class="mt-1 fw-bold text-danger alert alert-danger">
					<?= $validation['motivo']; ?>
				</div>
				<?php endif; ?>
			</div>
			<div class="mb-3">
				<label for="consulta" class="form-label  fw-bold">Consulta</label>
				<?php echo form_textarea([
					'name'        => 'consulta',
					'id'          => 'consulta',
					'class'       => 'form-control',
					'placeholder' => 'Ingrese su consulta', 'rows' => '3',
					'value'       => set_value('consulta')
				]); ?> 

				<?php if(! empty($validation['consulta'])): ?>
				<div class="mt-1 fw-bold text-danger alert alert-danger">
					<?= $validation['consulta']; ?>
				</div>
				<?php endif; ?>
			</div>
				<?php echo form_submit ('Consulta', 'Enviar', "class= 'btn btn-primary mt-3'"); ?>

			<?php echo form_close(); ?>
			</div>
			<div class="col-md-6">
					<h2 class="titulo-consulta text-center">Donde estamos</h2>
					<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d13064.87324385786!2d-58.763822438204194!3d-35.05128064559412!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2sar!4v1745330446075!5m2!1ses!2sar" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
		</div>
	</div>
</div>

<?php if (session()->getFlashdata('mensajeConsulta')): ?>
<script>
Swal.fire({
  title: '¡Éxito!',
  icon: 'success',
  text: '<?= session()->getFlashdata('mensajeConsulta'); ?>',
  confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>