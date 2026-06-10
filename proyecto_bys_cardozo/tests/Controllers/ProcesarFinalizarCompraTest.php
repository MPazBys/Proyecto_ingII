<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Config\Factories;
use App\Controllers\CarritoController;
use App\Models\libros_model;
use App\Models\venta_model;
use App\Models\detalle_venta_model;
use App\Models\persona_model;
use App\Models\direccion_model;

final class ProcesarFinalizarCompraTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Factories::reset();
    }

    /**
     * Helper centralizado para preparar mocks e invocar procesar_finalizar_compra.
     */
    private function ejecutarPruebaCompra(
        array $postData,
        array $cartItems = [],
        ?array $validationErrors = null,
        bool $smtpFails = false,
        bool $dbFails = false,
        array $libroData = ['idLibro' => 7, 'nombreLibro' => 'Libro 7', 'stockLibro' => 8]
    ) {
        // 1. Limpiar e inicializar el carrito
        $cart = \Config\Services::cart();
        $cart->destroy();
        foreach ($cartItems as $item) {
            $cart->insert($item);
        }

        // 2. Simular Sesión de Cliente (ID 13)
        $datosSesion = [
            'login'  => true,
            'id'     => 13,
            'perfil' => 2 // Perfil Cliente
        ];
        session()->set($datosSesion);

        // 3. Mock de Validación
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockValidation->method('withRequest')->willReturn($mockValidation);

        if ($validationErrors === null) {
            $mockValidation->method('run')->willReturn(true);
        } else {
            $mockValidation->method('run')->willReturn(false);
            $mockValidation->method('getErrors')->willReturn($validationErrors);
        }
        \Config\Services::injectMock('validation', $mockValidation);

        // 4. Mock de Servicio de Email
        $mockEmail = $this->getMockBuilder(\CodeIgniter\Email\Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmail->method('setTo')->willReturn($mockEmail);
        $mockEmail->method('setSubject')->willReturn($mockEmail);
        $mockEmail->method('setMessage')->willReturn($mockEmail);

        if ($smtpFails) {
            $mockEmail->method('send')->willReturn(false);
        } else {
            $mockEmail->method('send')->willReturn(true);
        }
        \Config\Services::injectMock('email', $mockEmail);

        // 5. Mock de Modelos
        // Persona Model
        $mockPersonaModel = $this->getMockBuilder(persona_model::class)->disableOriginalConstructor()->getMock();
        $mockPersonaModel->method('find')->with(13)->willReturn([
            'idPersona'      => 13,
            'nombrePersona'  => 'Juan',
            'apellidoPersona'=> 'Perez',
            'correoPersona'  => 'juan@example.com',
            'idDireccion'    => null
        ]);
        $mockPersonaModel->method('update')->willReturn(true);
        Factories::injectMock('models', persona_model::class, $mockPersonaModel);

        // Libros Model
        $mockLibrosModel = $this->getMockBuilder(libros_model::class)->disableOriginalConstructor()->getMock();
        $mockLibrosModel->method('find')->willReturn($libroData);
        $mockLibrosModel->method('update')->willReturn(true);
        Factories::injectMock('models', libros_model::class, $mockLibrosModel);

        // Direccion Model
        $mockDireccionModel = $this->getMockBuilder(direccion_model::class)->disableOriginalConstructor()->getMock();
        $mockDireccionModel->method('insert')->willReturn(10);
        $mockDireccionModel->method('update')->willReturn(true);
        Factories::injectMock('models', direccion_model::class, $mockDireccionModel);

        // Venta Model
        $mockVentaModel = $this->getMockBuilder(venta_model::class)->disableOriginalConstructor()->getMock();
        if ($dbFails) {
            $mockVentaModel->method('insert')->willThrowException(new \Exception("Error durante procesamiento de la venta."));
        } else {
            $mockVentaModel->method('insert')->willReturn(45); // Retorna ID autogenerado
        }
        Factories::injectMock('models', venta_model::class, $mockVentaModel);

        // Detalle Venta Model
        $mockDetalleModel = $this->getMockBuilder(detalle_venta_model::class)->disableOriginalConstructor()->getMock();
        $mockDetalleModel->method('insert')->willReturn(true);
        Factories::injectMock('models', detalle_venta_model::class, $mockDetalleModel);

        // Mocks adicionales para evitar dependencias de base de datos durante initController
        $mockFormaPagoModel = $this->getMockBuilder(\App\Models\formapago_model::class)->disableOriginalConstructor()->getMock();
        $mockFormaPagoModel->method('findAll')->willReturn([['idPago' => 1, 'nombrePago' => 'Efectivo'], ['idPago' => 2, 'nombrePago' => 'Tarjeta']]);
        Factories::injectMock('models', \App\Models\formapago_model::class, $mockFormaPagoModel);

        $mockProvinciaModel = $this->getMockBuilder(\App\Models\provincias_model::class)->disableOriginalConstructor()->getMock();
        $mockProvinciaModel->method('findAll')->willReturn([['idProvincia' => 1, 'nombreProvincia' => 'Corrientes']]);
        Factories::injectMock('models', \App\Models\provincias_model::class, $mockProvinciaModel);

        $mockLocalidadModel = $this->getMockBuilder(\App\Models\localidades_model::class)->disableOriginalConstructor()->getMock();
        $mockLocalidadModel->method('findAll')->willReturn([['idLocalidad' => 1, 'nombreLocalidad' => 'Corrientes Capital', 'idProvincia' => 1]]);
        Factories::injectMock('models', \App\Models\localidades_model::class, $mockLocalidadModel);

        // 6. Mock de Request
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([config(\Config\App::class), $this->uri, null, new \CodeIgniter\HTTP\UserAgent()])
            ->onlyMethods(['getPost'])
            ->getMock();
        $request->method('getPost')->willReturnCallback(
            fn ($campo = null) => $campo === null ? $postData : ($postData[$campo] ?? null)
        );

        // 7. Ejecutar
        return $this->withRequest($request)
            ->controller(CarritoController::class)
            ->execute('procesar_finalizar_compra');
    }

    // =========================================================================
    // FILA 2: Campos completos, stock disponible y correo SMTP funcionando -> Exitosa
    // =========================================================================
    public function testProcesarCompraExitoso()
    {
        $cartItems = [[
            'id'    => 7,
            'name'  => 'Percy Jackson',
            'price' => 18500.50,
            'qty'   => 1,
            'stockLibro' => 8
        ]];

        $postData = [
            'selectedFormaEnvio' => '2', // Domicilio
            'calle'              => 'San Martín',
            'altura'             => '1458',
            'provincia'          => '1',
            'idLocalidad'        => '1',
            'pisoDepto'          => '',
            'consideraciones'    => '',
            'selectedFormaPago'  => '2', // Tarjeta
            'tarjeta'            => '1234567890123654',
            'vencimiento'        => '12/26',
            'cvv'                => '123',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gracias_por_tu_compra'));
        $this->assertEquals('¡Compra realizada con éxito!', session()->getFlashdata('mensaje'));
    }

    // =========================================================================
    // FILA 3: Intentar finalizar compra con el carrito de compras vacío -> Error de validación
    // =========================================================================
    public function testProcesarCompraCarritoVacio()
    {
        $postData = [
            'selectedFormaEnvio' => '2',
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        // Carrito vacío []
        $resultado = $this->ejecutarPruebaCompra($postData, []);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('ver_carrito'));
        $this->assertEquals('Tu carrito está vacío. Agrega productos antes de finalizar la compra.', session()->getFlashdata('msj'));
    }

    // =========================================================================
    // FILA 4: Cantidad solicitada supera las existencias físicas -> Error de validación (Stock insuficiente)
    // =========================================================================
    public function testProcesarCompraStockInsuficiente()
    {
        $cartItems = [[
            'id'    => 7,
            'name'  => 'Percy Jackson',
            'price' => 18500.50,
            'qty'   => 10, // Pide 10 unidades
            'stockLibro' => 8
        ]];

        $postData = [
            'selectedFormaEnvio' => '1', // Sucursal
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, null, false, false, [
            'idLibro' => 7, 'nombreLibro' => 'Percy Jackson', 'stockLibro' => 8 // Stock en base de datos es 8
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('ver_carrito'));
        $this->assertEquals('Lo sentimos, el stock de "Percy Jackson" ya no está disponible. Por favor, revisa tu carrito.', session()->getFlashdata('msj'));
    }

    // =========================================================================
    // FILA 5: Ausencia de selección en el tipo de envío obligatorio -> Error de validación
    // =========================================================================
    public function testProcesarCompraEnvioNoSeleccionado()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '', // Sin seleccionar
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'selectedFormaEnvio' => 'Debe seleccionar una forma de envío.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 6: Envío a domicilio seleccionado pero con dirección incompleta -> Error de validación
    // =========================================================================
    public function testProcesarCompraDireccionIncompleta()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2', // Domicilio
            'calle'              => '', // Vacío
            'altura'             => '', // Vacío
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'calle'  => 'La calle es obligatoria.',
            'altura' => 'La altura es obligatoria.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 7: Calle con formato alfabético/numérico erróneo -> Error de validación
    // =========================================================================
    public function testProcesarCompraCalleSoloNumerica()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => '12345', // Solo números
            'altura'             => '1458',
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'calle' => 'La calle debe contener texto alfabético.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 8: Altura con longitud <3 -> Error de validación
    // =========================================================================
    public function testProcesarCompraAlturaMuyCorta()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => '0', // <3 dígitos
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'altura' => 'La altura debe tener entre 3 y 5 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 9: Altura con longitud >5 -> Error de validación
    // =========================================================================
    public function testProcesarCompraAlturaMuyLarga()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => '125660', // >5 dígitos
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'altura' => 'La altura debe tener entre 3 y 5 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 10: Altura con letras en vez de números -> Error de validación
    // =========================================================================
    public function testProcesarCompraAlturaNoNumerica()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => 'ab', // Letras
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'altura' => 'La altura debe tener entre 3 y 5 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 11: Datos de domicilio sin selección de localidad -> Error de validación
    // =========================================================================
    public function testProcesarCompraSinSeleccionarLocalidad()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => '1458',
            'idLocalidad'        => '', // Sin seleccionar
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'idLocalidad' => 'La ciudad es obligatoria.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 12: Datos de domicilio sin selección de provincia -> Error de validación
    // =========================================================================
    public function testProcesarCompraSinSeleccionarProvincia()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => '1458',
            'provincia'          => '', // Sin seleccionar
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'provincia' => 'La provincia es obligatoria.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 13: Datos de domicilio: Piso/Depto con formato incorrecto -> Error de validación
    // =========================================================================
    public function testProcesarCompraPisoDeptoFormatoIncorrecto()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => '1458',
            'pisoDepto'          => '45', // Formato numérico puro sin letras
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'pisoDepto' => 'El piso/depto debe contener al menos una letra.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 14: Datos de domicilio: Consideraciones con formato incorrecto -> Error de validación
    // =========================================================================
    public function testProcesarCompraConsideracionesFormatoIncorrecto()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '2',
            'calle'              => 'San Martín',
            'altura'             => '1458',
            'consideraciones'    => '5', // Formato numérico puro sin letras
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'consideraciones' => 'Las consideraciones deben contener al menos una letra.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 15: Ausencia de selección en el método de pago obligatorio -> Error de validación
    // =========================================================================
    public function testProcesarCompraPagoNoSeleccionado()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '', // Sin seleccionar
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'selectedFormaPago' => 'Debe seleccionar una forma de pago.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 16: Pago con tarjeta seleccionado pero con campos electrónicos vacíos -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaCamposVacios()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2', // Tarjeta
            'tarjeta'            => '', // Vacíos
            'vencimiento'        => '',
            'cvv'                => '',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'tarjeta'     => 'El número de tarjeta es obligatorio.',
            'vencimiento' => 'La fecha de vencimiento es obligatoria.',
            'cvv'         => 'El CVV es obligatorio.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 17: Datos de tarjeta inválidos (número de tarjeta mayor a 16 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaDigitosExcesivos()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2',
            'tarjeta'            => '12345678901234567', // 17 dígitos
            'vencimiento'        => '12/26',
            'cvv'                => '123',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'tarjeta' => 'La tarjeta debe tener exactamente 16 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 18: Datos de tarjeta inválidos (número de tarjeta menor a 16 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaDigitosInsuficientes()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2',
            'tarjeta'            => '123456789012345', // 15 dígitos
            'vencimiento'        => '12/26',
            'cvv'                => '123',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'tarjeta' => 'La tarjeta debe tener exactamente 16 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 19: Datos de tarjeta inválidos (Mes inexistente) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaMesInexistente()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2',
            'tarjeta'            => '1234567890123456',
            'vencimiento'        => '13/26', // Mes 13
            'cvv'                => '123',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'vencimiento' => 'El formato de vencimiento debe ser numérico MM/AA (Por ejemplo: 06/26).'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 20: Datos de tarjeta inválidos (tarjeta vencida) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaVencida()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2',
            'tarjeta'            => '1234567890123456',
            'vencimiento'        => '03/26', // Simulación vencida
            'cvv'                => '123',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'vencimiento' => 'La tarjeta se encuentra vencida.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 21: Datos de tarjeta inválidos (CVV menor a 3 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaCvvCorto()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2',
            'tarjeta'            => '1234567890123456',
            'vencimiento'        => '12/26',
            'cvv'                => '12', // 2 dígitos
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'cvv' => 'El CVV debe tener 3 o 4 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 22: Datos de tarjeta inválidos (CVV mayor a 4 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTarjetaCvvLargo()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '2',
            'tarjeta'            => '1234567890123456',
            'vencimiento'        => '12/26',
            'cvv'                => '12564', // 5 dígitos
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'cvv' => 'El CVV debe tener 3 o 4 dígitos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 23: Campos obligatorios de contacto personal vacíos -> Error de validación
    // =========================================================================
    public function testProcesarCompraContactoVacios()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '1',
            'telefono'           => '', // Vacío
            'dni'                => ''  // Vacío
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'telefono' => 'El teléfono es obligatorio.',
            'dni'      => 'El DNI es obligatorio.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 24: Longitud de DNI (>9 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraDniExcesivo()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '111111111111' // 12 dígitos
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'dni' => 'El DNI debe tener entre 7 y 9 dígitos numéricos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 25: Longitud de DNI (<7 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraDniInsuficiente()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '1',
            'telefono'           => '3794123456',
            'dni'                => '1111' // 4 dígitos
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'dni' => 'El DNI debe tener entre 7 y 9 dígitos numéricos.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 26: Longitud de Teléfono (<10 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTelefonoInsuficiente()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '1',
            'telefono'           => '3794', // 4 dígitos
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'telefono' => 'El teléfono debe tener entre 10 y 15 dígitos numéricos sin 0 ni 15.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 27: Longitud de Teléfono (>15 dígitos) -> Error de validación
    // =========================================================================
    public function testProcesarCompraTelefonoExcesivo()
    {
        $cartItems = [['id' => 7, 'name' => 'Percy Jackson', 'price' => 18500.50, 'qty' => 1, 'stockLibro' => 8]];
        $postData = [
            'selectedFormaEnvio' => '1',
            'selectedFormaPago'  => '1',
            'telefono'           => '3794989889895698', // 16 dígitos
            'dni'                => '35444111'
        ];

        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, [
            'telefono' => 'El teléfono debe tener entre 10 y 15 dígitos numéricos sin 0 ni 15.'
        ]);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertSessionHas('errors');
    }

    // =========================================================================
    // FILA 28: Fallo SMTP: Conexión rechazada -> Error de sistema (rollback)
    // =========================================================================
    public function testProcesarCompraFalloSMTP()
    {
        $cartItems = [[
            'id'    => 7,
            'name'  => 'Percy Jackson',
            'price' => 18500.50,
            'qty'   => 1,
            'stockLibro' => 8
        ]];

        $postData = [
            'selectedFormaEnvio' => '2', // Domicilio
            'calle'              => 'San Martín',
            'altura'             => '1458',
            'provincia'          => '1',
            'idLocalidad'        => '1',
            'pisoDepto'          => '',
            'consideraciones'    => '',
            'selectedFormaPago'  => '2', // Tarjeta
            'tarjeta'            => '1234567890123654',
            'vencimiento'        => '12/26',
            'cvv'                => '123',
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        // smtpFails = true
        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, null, true);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('ver_carrito'));
        $this->assertEquals(
            'No se pudo enviar el correo de comprobante, compra cancelada por seguridad.',
            session()->getFlashdata('msj')
        );
    }

    // =========================================================================
    // FILA 29: Excepción imprevista o quiebre en DB -> Error durante procesamiento de la venta
    // =========================================================================
    public function testProcesarCompraFallaPersistenciaSQL()
    {
        $cartItems = [[
            'id'    => 7,
            'name'  => 'Percy Jackson',
            'price' => 18500.50,
            'qty'   => 1,
            'stockLibro' => 8
        ]];

        $postData = [
            'selectedFormaEnvio' => '1', // Sucursal
            'selectedFormaPago'  => '1', // Efectivo
            'telefono'           => '3794123456',
            'dni'                => '35444111'
        ];

        // dbFails = true (ventaModel->insert() lanzará excepción)
        $resultado = $this->ejecutarPruebaCompra($postData, $cartItems, null, false, true);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('ver_carrito'));
        $this->assertEquals(
            'Error durante procesamiento de la venta.',
            session()->getFlashdata('msj')
        );
    }
}