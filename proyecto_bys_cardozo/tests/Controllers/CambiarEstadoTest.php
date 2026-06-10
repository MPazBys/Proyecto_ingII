<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Config\Factories;
use CodeIgniter\Email\Email;
use App\Controllers\VentaController;
use App\Models\venta_model;
use App\Models\persona_model;

final class CambiarEstadoTest extends CIUnitTestCase
{
    use ControllerTestTrait; // Permite ejecutar el controlador simulando el flujo HTTP completo

    protected function setUp(): void
    {
        parent::setUp();
        // Limpiamos los mocks registrados en las factorías antes de cada prueba
        Factories::reset();
    }

    // ==========================================
    // 1. TEST 1: Cambiar estado de 'Pendiente' a 'Finalizado' con forma de envío 'Retiro en sucursal' (Éxito)
    // ==========================================
    // Siendo un pedido con forma de envío 'Retiro en sucursal', el patrón Estado debería permitir cambiar a 'Finalizado',
    // por lo que el método debería retornar un mensaje de éxito y realizar la actualización.
    public function testCambiarEstadoPendienteAFinalizadoExitoso()
    {
        // ==========================================
        // 1. MOCK DE LOS MODELOS (PHPUnit)
        // ==========================================
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // ==========================================
        // 2. CONFIGURAR COMPORTAMIENTO DE LOS MOCKS
        // ==========================================
        // Simulamos que la venta existe en base de datos
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Pendiente',
            'idCliente'  => 10,
            'formaEnvio' => 1 // 1 = Retiro en sucursal (para que el patrón Estado permita ir a 'Finalizado')
        ]);

        // Simulamos que el cliente existe
        $mockPersonaModel->method('find')->willReturn([
            'idPersona'     => 10,
            'nombrePersona' => 'Juan',
            'apellidoPersona' => 'Pérez',
            'correoPersona' => 'juan@example.com'
        ]);

        // Simulamos que la actualización del estado retorna true (éxito)
        $mockVentaModel->method('update')->willReturn(true);

        // ==========================================
        // 3. INYECTAR LOS MOCKS EN LAS FACTORÍAS DE CI4
        // ==========================================
        // Esto intercepta cualquier llamada a 'model(venta_model::class)'
        // que ocurra dentro de VentaController::initController()
        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);

        // ==========================================
        // 4. MOCK DEL SERVICIO DE EMAIL (Evitar errores SMTP)
        // ==========================================
        // Como 'EstadoFinalizado' dispara un envío de mail usando Services::email(),
        // mockeamos el servicio de correo para que no intente conectarse al servidor real.
        $mockEmail = $this->getMockBuilder(Email::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $mockEmail->method('setTo')->willReturn($mockEmail);
        $mockEmail->method('setSubject')->willReturn($mockEmail);
        $mockEmail->method('setMessage')->willReturn($mockEmail);
        $mockEmail->method('send')->willReturn(true); // Simulamos envío exitoso

        // Inyectamos el mock en el contenedor de Servicios de CI4
        \Config\Services::injectMock('email', $mockEmail);

        // ==========================================
        // 5. SIMULAR LA SESIÓN (Admin autenticado)
        // ==========================================
        $datosSesion = [
            'login'  => true,
            'perfil' => 1,
            'nombre' => 'Administrador Test'
        ];
        session()->set($datosSesion);

        // ==========================================
        // 6. EJECUTAR EL CONTROLADOR
        // ==========================================
        // Usamos controller() y execute() para simular el ciclo de vida del controlador
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Finalizado');

        // ==========================================
        // 7. ASERCIONES (Verificaciones)
        // ==========================================
        // Verificamos que retorne un HTTP Redirect
        $this->assertTrue($resultado->isRedirect());
        
        // Verificamos el destino de la redirección
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));

        // Verificamos que se haya seteado la alerta de éxito en la sesión flash
        $this->assertEquals(
            'El pedido #1 cambió a Finalizado con éxito.', 
            session()->getFlashdata('mensaje')
        );
    }

    // ==========================================
    // 2. TEST 2: Cambiar estado de 'Pendiente' a 'Enviado' con forma de envío 'Retiro en sucursal' (Fallo)
    // ==========================================
    // Siendo un pedido con forma de envío 'Retiro en sucursal', el patrón Estado no debería permitir cambiar a 'Enviado',
    // por lo que el método debería retornar un error y no realizar la actualización.
    public function testCambiarEstadoPendienteAEnviadoFallido()
    {
        // 1. MOCK DE LOS MODELOS (PHPUnit)
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // Simulamos que la venta existe en base de datos
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Pendiente',
            'idCliente'  => 10,
            'formaEnvio' => 1 // 1 = Retiro en sucursal (para que el patrón Estado no permita ir a 'Enviado')
        ]);

        // Simulamos que el cliente existe
        $mockPersonaModel->method('find')->willReturn([
            'idPersona'     => 10,
            'nombrePersona' => 'Juan',
            'apellidoPersona' => 'Pérez',
            'correoPersona' => 'juan@example.com'
        ]);

        // Simulamos que la actualización del estado retorna true (éxito)
        $mockVentaModel->method('update')->willReturn(true);

        // 3. INYECTAR LOS MOCKS EN LAS FACTORÍAS DE CI4
        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);

        // 4. MOCK DEL SERVICIO DE EMAIL (Evitar errores SMTP)
        $mockEmail = $this->getMockBuilder(Email::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $mockEmail->method('setTo')->willReturn($mockEmail);
        $mockEmail->method('setSubject')->willReturn($mockEmail);
        $mockEmail->method('setMessage')->willReturn($mockEmail);
        $mockEmail->method('send')->willReturn(true); // Simulamos envío exitoso

        // Inyectamos el mock en el contenedor de Servicios de CI4
        \Config\Services::injectMock('email', $mockEmail);

        // 5. SIMULAR LA SESIÓN (Admin autenticado)
        $datosSesion = [
            'login'  => true,
            'perfil' => 1,
            'nombre' => 'Administrador Test'
        ];
        session()->set($datosSesion);

        // Usamos controller() y execute() para simular el ciclo de vida del controlador
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Enviado');


        // Verificamos que retorne un HTTP Redirect
        $this->assertTrue($resultado->isRedirect());
        
        // Verificamos el destino de la redirección
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));

        // Verificamos que se haya seteado la alerta de error en la sesión flash
        $this->assertEquals(
            'Transición de estado denegada por reglas de negocio del despacho.', 
            session()->getFlashdata('msj')
        );
    }

    // ==========================================
    // 3. TEST 3: Cambiar estado de 'Pendiente' a 'Enviado' con forma de envío 'Envío a domicilio' (Éxito)
    // ==========================================
    // Siendo un pedido con forma de envío 'Envío a domicilio', el patrón Estado debería permitir cambiar a 'Finalizado',
    // por lo que el método debería retornar mensaje de éxito y realizar la actualización.
    public function testCambiarEstadoPendienteAEnviadoExitoso()
    {
        // 1. MOCK DE LOS MODELOS (PHPUnit)
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // 2. Simulamos que la venta existe en base de datos
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Pendiente',
            'idCliente'  => 10,
            'formaEnvio' => 2 // 2 = Envío a domicilio (para que el patrón Estado permita ir a 'Enviado')
        ]);

        // Simulamos que el cliente existe
        $mockPersonaModel->method('find')->willReturn([
            'idPersona'     => 10,
            'nombrePersona' => 'Juan',
            'apellidoPersona' => 'Pérez',
            'correoPersona' => 'juan@example.com'
        ]);

        // Simulamos que la actualización del estado retorna true (éxito)
        $mockVentaModel->method('update')->willReturn(true);

        // 3. INYECTAR LOS MOCKS EN LAS FACTORÍAS DE CI4
        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);

        // 4. MOCK DEL SERVICIO DE EMAIL (Evitar errores SMTP)
        $mockEmail = $this->getMockBuilder(Email::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $mockEmail->method('setTo')->willReturn($mockEmail);
        $mockEmail->method('setSubject')->willReturn($mockEmail);
        $mockEmail->method('setMessage')->willReturn($mockEmail);
        $mockEmail->method('send')->willReturn(true); // Simulamos envío exitoso

        // Inyectamos el mock en el contenedor de Servicios de CI4
        \Config\Services::injectMock('email', $mockEmail);

        // 5. SIMULAR LA SESIÓN (Admin autenticado)
        $datosSesion = [
            'login'  => true,
            'perfil' => 1,
            'nombre' => 'Administrador Test'
        ];
        session()->set($datosSesion);

        // 6. Usamos controller() y execute() para simular el ciclo de vida del controlador
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Enviado');


        // Verificamos que retorne un HTTP Redirect
        $this->assertTrue($resultado->isRedirect());
        
        // Verificamos el destino de la redirección
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));

        // Verificamos que se haya seteado la alerta de éxito en la sesión flash
        $this->assertEquals(
            'El pedido #1 cambió a Enviado con éxito.', 
            session()->getFlashdata('mensaje')
        );
    }

    // ==========================================
    // 4. TEST 4: Cambiar estado de 'Enviado' a 'Finalizado' con forma de envío 'Envío a domicilio (Éxito)'
    // ==========================================
    // Siendo un pedido con forma de envío 'Envío a domicilio', el patrón Estado debería permitir cambiar a 'Enviado',
    // por lo que el método debería retornar mensaje de éxito y realizar la actualización.
    public function testCambiarEstadoEnviadoAFinalizadoExitoso()
    {
        // 1. MOCK DE LOS MODELOS (PHPUnit)
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // 2. Simulamos que la venta existe en base de datos
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Enviado',
            'idCliente'  => 10,
            'formaEnvio' => 2 // 2 = Envío a domicilio (para que el patrón Estado permita ir a 'Finalizado')
        ]);

        // Simulamos que el cliente existe
        $mockPersonaModel->method('find')->willReturn([
            'idPersona'     => 10,
            'nombrePersona' => 'Juan',
            'apellidoPersona' => 'Pérez',
            'correoPersona' => 'juan@example.com'
        ]);

        // Simulamos que la actualización del estado retorna true (éxito)
        $mockVentaModel->method('update')->willReturn(true);

        // 3. INYECTAR LOS MOCKS EN LAS FACTORÍAS DE CI4
        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);

        // 4. MOCK DEL SERVICIO DE EMAIL (Evitar errores SMTP)
        $mockEmail = $this->getMockBuilder(Email::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $mockEmail->method('setTo')->willReturn($mockEmail);
        $mockEmail->method('setSubject')->willReturn($mockEmail);
        $mockEmail->method('setMessage')->willReturn($mockEmail);
        $mockEmail->method('send')->willReturn(true); // Simulamos envío exitoso

        // Inyectamos el mock en el contenedor de Servicios de CI4
        \Config\Services::injectMock('email', $mockEmail);


        // 5. SIMULAR LA SESIÓN (Admin autenticado)
        $datosSesion = [
            'login'  => true,
            'perfil' => 1,
            'nombre' => 'Administrador Test'
        ];
        session()->set($datosSesion);


        // Usamos controller() y execute() para simular el ciclo de vida del controlador
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Finalizado');


        // Verificamos que retorne un HTTP Redirect
        $this->assertTrue($resultado->isRedirect());
        
        // Verificamos el destino de la redirección
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));

        // Verificamos que se haya seteado la alerta de éxito en la sesión flash
        $this->assertEquals(
            'El pedido #1 cambió a Finalizado con éxito.', 
            session()->getFlashdata('mensaje')
        );
    }

    // ==========================================
    // 5. TEST 5: Cambiar estado de 'Pendiente' a 'Finalizado' con forma de envío 'Envío a domicilio (Fallo)'
    // ==========================================
    // Siendo un pedido con forma de envío 'Envío a domicilio', el patrón Estado no debería permitir cambiar a 'Finalizado',
    // por lo que el método debería retornar mensaje de error y no realizar la actualización.
    public function testCambiarEstadoPendienteAFinalizadoFallido()
    {
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // Simulamos que la venta existe en base de datos
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Pendiente',
            'idCliente'  => 10,
            'formaEnvio' => 2 // 2 = Envío a domicilio (para que el patrón Estado permita ir a 'Enviado')
        ]);

        // Simulamos que el cliente existe
        $mockPersonaModel->method('find')->willReturn([
            'idPersona'     => 10,
            'nombrePersona' => 'Juan',
            'apellidoPersona' => 'Pérez',
            'correoPersona' => 'juan@example.com'
        ]);

        // Simulamos que la actualización del estado retorna true (éxito)
        $mockVentaModel->method('update')->willReturn(true);


        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);


        $mockEmail = $this->getMockBuilder(Email::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $mockEmail->method('setTo')->willReturn($mockEmail);
        $mockEmail->method('setSubject')->willReturn($mockEmail);
        $mockEmail->method('setMessage')->willReturn($mockEmail);
        $mockEmail->method('send')->willReturn(true); // Simulamos envío exitoso

        // Inyectamos el mock en el contenedor de Servicios de CI4
        \Config\Services::injectMock('email', $mockEmail);


        $datosSesion = [
            'login'  => true,
            'perfil' => 1,
            'nombre' => 'Administrador Test'
        ];
        session()->set($datosSesion);

        
        // Usamos controller() y execute() para simular el ciclo de vida del controlador
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Finalizado');


        // Verificamos que retorne un HTTP Redirect
        $this->assertTrue($resultado->isRedirect());
        
        // Verificamos el destino de la redirección
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));

        // Verificamos que se haya seteado la alerta de error en la sesión flash
        $this->assertEquals(
            'Transición de estado denegada por reglas de negocio del despacho.', 
            session()->getFlashdata('msj')
        );
    }

    // ==========================================
    // 6. TEST 6: Cambiar estado de una venta ya 'Finalizada' (Fallo)
    // ==========================================
    // Al ser 'Finalizado' un estado terminal, el patrón Estado no debería permitir 
    // ninguna transición posterior, retornando error.
    public function testCambiarEstadoVentaYaFinalizadaFallido()
    {
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // Simulamos que la venta ya está en estado Finalizado
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Finalizado',
            'idCliente'  => 10,
            'formaEnvio' => 2
        ]);

        $mockPersonaModel->method('find')->willReturn([
            'idPersona'     => 10,
            'nombrePersona' => 'Juan',
            'apellidoPersona' => 'Pérez',
            'correoPersona' => 'juan@example.com'
        ]);

        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);

        $datosSesion = ['login' => true, 'perfil' => 1];
        session()->set($datosSesion);

        // Intentamos cambiar de Finalizado a Enviado (debería denegarse)
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Enviado');

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));
        $this->assertEquals(
            'Transición de estado denegada por reglas de negocio del despacho.', 
            session()->getFlashdata('msj')
        );
    }

    // ==========================================
    // 7. TEST 7: Cambiar estado sin sesión de Admin (Fallo)
    // ==========================================
    public function testCambiarEstadoSinSesionAdminRebota()
    {
        // 1. Nos aseguramos de limpiar la sesión actual
        session()->destroy();

        // 2. Ejecutamos el método
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Finalizado');

        // 3. Verificamos que redirija al Login con mensaje de error
        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('login'));
        $this->assertEquals(
            'Acceso no autorizado.', 
            session()->getFlashdata('error_login')
        );
    }

    // ==========================================
    // 8. TEST 8: Cambiar estado de una venta inexistente (Fallo)
    // ==========================================
    public function testCambiarEstadoVentaInexistente()
    {
        // 1. Mock de VentaModel que retorna null en find()
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $mockVentaModel->method('find')->willReturn(null); // Venta no encontrada

        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);

        // 2. Simular sesión de Admin
        $datosSesion = ['login' => true, 'perfil' => 1];
        session()->set($datosSesion);

        // 3. Ejecutar controlador con un ID de venta inexistente (ej: 999)
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 999, 'Finalizado');

        // 4. Verificaciones
        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));
        $this->assertEquals(
            'La venta no existe.', 
            session()->getFlashdata('msj')
        );
    }

    // ==========================================
    // 9. TEST 9: Cambiar estado cuando el cliente no existe (Fallo)
    // ==========================================
    public function testCambiarEstadoClienteNoExiste()
    {
        // 1. Mock de VentaModel (SÍ encuentra la venta)
        $mockVentaModel = $this->getMockBuilder(venta_model::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $mockVentaModel->method('find')->willReturn([
            'idVenta'    => 1,
            'estado'     => 'Pendiente',
            'idCliente'  => 999, // ID de un cliente que no existe
            'formaEnvio' => 1
        ]);

        // 2. Mock de PersonaModel (NO encuentra al cliente)
        $mockPersonaModel = $this->getMockBuilder(persona_model::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $mockPersonaModel->method('find')->willReturn(null); // Cliente no encontrado

        Factories::injectMock('models', \App\Models\venta_model::class, $mockVentaModel);
        Factories::injectMock('models', \App\Models\persona_model::class, $mockPersonaModel);

        // 3. Simular sesión de Admin
        $datosSesion = ['login' => true, 'perfil' => 1];
        session()->set($datosSesion);

        // 4. Ejecutar controlador
        $resultado = $this->controller(VentaController::class)
                          ->execute('cambiar_estado', 1, 'Finalizado');

        // 5. Verificaciones
        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gestionar_ventas'));
        $this->assertEquals(
            'Cliente no encontrado.', 
            session()->getFlashdata('msj')
        );
    }
}