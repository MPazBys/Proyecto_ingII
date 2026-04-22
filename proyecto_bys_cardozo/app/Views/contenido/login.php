<div class="library-auth" id="authContainer">
  <div class="auth-card login">
    <h2>📚 Bienvenido a Libros M&P</h2>
    <p>Iniciá sesión para seguir explorando historias</p>
    <?php echo form_open('verificar_usuario') ?>
      <?php if (isset($error_login)) : ?>
        <div class="fw-bold alert alert-danger">
          <?= esc($error_login) ?>
        </div>
      <?php endif; ?>

    <div class="mb-1">
        <?php echo form_input([
            'name'        => 'correo',
            'id'          => 'correo',
            'type'        => 'email', 
            'class'       => 'form-control',
            'placeholder' => 'Ingrese su correo electrónico',
            'value'       => set_value('correo')
        ]); ?>
    </div>
    <div class="mb-1">
        <?php echo form_password([ 
            'name'        => 'password', 
            'id'          => 'password',
            'class'       => 'form-control',
            'placeholder' => 'Ingrese su contraseña',
            'value'       => set_value('password')
        ]); ?>
    </div>
    <?php echo form_submit('btn_registrar', 'Iniciar sesion', "class='btn btn-success'"); ?>
    <?php echo form_close(); ?>
    <p>¿No tenés cuenta? <a href="<?= ('registro'); ?>" id="showRegister">Registrate</a></p>
  </div>

</div>

<?php if (session()->getFlashdata('mensaje')): ?>
<script>
Swal.fire({
  title: '¡Éxito!',
  icon: 'success',
  text: '<?= session()->getFlashdata('mensaje'); ?>',
  confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>