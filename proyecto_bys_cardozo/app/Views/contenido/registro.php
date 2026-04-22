<div class="library-auth" id="authContainer">
    
  <div class="auth-card register">
    <h2>🖋️ Crear cuenta en Libros M&P</h2>
    <p>Unite a nuestra comunidad de lectores</p>
    
    <?php echo form_open('registro_usuario') ?>
    <div class="mb-1">
        <?php echo form_input([
            'name'        => 'nombre',
            'id'          => 'nombre',
            'type'        => 'text',
            'class'       => 'form-control',
            'placeholder' => 'Ingrese su nombre',
            'value'       => set_value('nombre')
        ]); ?>
        <?php if(! empty($validation['nombre'])): ?>
          <div class="mt-1 fw-bold text-danger alert alert-danger">
            <?= $validation['nombre']; ?>
          </div>
        <?php endif; ?>
    </div>
    <div class="mb-1">
        <?php echo form_input([
            'name'        => 'apellido',
            'id'          => 'apellido',
            'type'        => 'text',
            'class'       => 'form-control',
            'placeholder' => 'Ingrese su apellido',
            'value'       => set_value('apellido')
        ]); ?>
        <?php if(! empty($validation['apellido'])): ?>
          <div class="mt-1 fw-bold text-danger alert alert-danger">
            <?= $validation['apellido']; ?>
          </div>
        <?php endif; ?>
    </div>
    <div class="mb-1">
        <?php echo form_input([
            'name'        => 'correo',
            'id'          => 'correo',
            'type'        => 'email', 
            'class'       => 'form-control',
            'placeholder' => 'Ingrese su correo electrónico',
            'value'       => set_value('correo')
        ]); ?>
        <?php if(! empty($validation['correo'])): ?>
          <div class="mt-1 fw-bold text-danger alert alert-danger">
            <?= $validation['correo']; ?>
          </div>
        <?php endif; ?>
    </div>
    <div class="mb-1">
        <?php echo form_password([ 
            'name'        => 'password', 
            'id'          => 'password',
            'class'       => 'form-control',
            'placeholder' => 'Ingrese su contraseña',
            'value'       => set_value('password')
        ]); ?>
        <?php if(! empty($validation['password'])): ?>
          <div class="mt-1 fw-bold text-danger alert alert-danger">
            <?= $validation['password']; ?>
          </div>
        <?php endif; ?>
    </div>
    <div class="mb-1">
        <?php echo form_password([ 
            'name'        => 'confirm_password', 
            'id'          => 'confirm_password',
            'class'       => 'form-control',
            'placeholder' => 'Reingrese su contraseña',
            'value'       => set_value('confirm_password')
        ]); ?>
        <?php if(! empty($validation['confirm_password'])): ?>
          <div class="mt-1 fw-bold text-danger alert alert-danger">
            <?= $validation['confirm_password']; ?>
          </div>
        <?php endif; ?>
    </div>
    <?php echo form_submit('btn_registrar', 'Registrarme', "class='btn btn-success'"); ?>
<?php echo form_close(); ?>
    <p>¿Ya tenés cuenta? <a href="<?= ('login'); ?>" id="showRegister">Iniciar Sesion</a></p>
  </div>
</div>
