<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Validation\Validation;
use App\Controllers\LibroController;
use App\Models\libros_model;
use App\Models\categorias_model;
use App\Models\autores_model;
use App\Models\etiqueta_model;

final class RegistrarLibroTest extends CIUnitTestCase
{
    use ControllerTestTrait; // Permite simular llamadas HTTP directas al controlador

    protected function setUp(): void
    {
        parent::setUp();
        // Limpiamos los mocks registrados en las factorías antes de cada test
        Factories::reset();
    }

    // =========================================================================
    // 1. Test 1: Registro Exitoso de un Libro
    // =========================================================================
    public function testRegistrarLibroExitoso()
    {
        // ==========================================================
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        // ==========================================================
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(true);

        \Config\Services::injectMock('validation', $mockValidation);


        // ==========================================================
        // 2. MOCK DEL MODELO
        // ==========================================================
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLibroModel->method('insert')
            ->willReturn(true);

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );


        // ==========================================================
        // 3. MOCK DEL ARCHIVO SUBIDO
        // ==========================================================
        // Pasamos argumentos ficticios al constructor para SplFileInfo interno
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();

        $mockFile->method('isValid')
            ->willReturn(true);

        $mockFile->method('hasMoved')
            ->willReturn(false);

        $mockFile->method('getRandomName')
            ->willReturn('test_image.jpg');

        $mockFile->method('move')
            ->willReturn(true);


        // ==========================================================
        // 4. DATOS DEL FORMULARIO
        // ==========================================================
        $postData = [
            'titulo'       => 'Percy Jackson',
            'autor'        => 1,
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];


        // ==========================================================
        // 5. MOCK DE LA REQUEST
        // ==========================================================
        // Instanciamos el UserAgent real para cumplir con la firma del constructor
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        // Cuando el controlador haga: $this->request->getFile('imagen')
        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        // Cuando el controlador haga: $this->request->getPost('campo')
        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );


        // ==========================================================
        // 6. EJECUTAR EL CONTROLADOR
        // ==========================================================
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');


        // ==========================================================
        // 7. VERIFICACIONES
        // ==========================================================
        $this->assertTrue($resultado->isRedirect());

        $resultado->assertRedirectTo(base_url('gestionar'));

        $this->assertEquals(
            '¡El libro se registró correctamente!',
            session()->getFlashdata('mensaje')
        );
    }

    // =========================================================================
    // 2. TEST 2: Registro con Campo Titulo Vacio (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroTituloVacio()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'titulo'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'titulo');

        // Retorna el mensaje de error correspondiente solo para 'titulo'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'titulo' ? 'El título es obligatorio' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['titulo' => 'El título es obligatorio']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => '', // Vacío para provocar el fallo
            'autor'        => 1,
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El título es obligatorio');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 3. TEST 3: Registro con Campo Titulo Corto (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroTituloCorto()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'titulo'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'titulo');

        // Retorna el mensaje de error correspondiente solo para 'titulo'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'titulo' ? 'El título debe tener al menos 3 caracteres' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['titulo' => 'El título debe tener al menos 3 caracteres']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Ab', // Demasiado corto para provocar el fallo
            'autor'        => 1,
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El título debe tener al menos 3 caracteres');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 4. TEST 4: Registro con Campo Titulo Largo (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroTituloLargo()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'titulo'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'titulo');

        // Retorna el mensaje de error correspondiente solo para 'titulo'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'titulo' ? 'El título no puede superar los 100 caracteres' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['titulo' => 'El título no puede superar los 100 caracteres']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => str_repeat('A', 101), // Título demasiado largo para provocar el fallo
            'autor'        => 1,
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El título no puede superar los 100 caracteres');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 5. TEST 5: Registro con Sin Autor (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroSinAutor()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'autor'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'autor');

        // Retorna el mensaje de error correspondiente solo para 'autor'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'autor' ? 'Debe seleccionar un autor' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['autor' => 'Debe seleccionar un autor']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '', // Sin autor para provocar el fallo
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('Debe seleccionar un autor');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 6. TEST 6: Registro con Descripción Vacía (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroDescripcionVacia()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'descripcion'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'descripcion');

        // Retorna el mensaje de error correspondiente solo para 'descripcion'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'descripcion' ? 'La descripción es requerida' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['descripcion' => 'La descripción es requerida']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => '', // Sin descripción para provocar el fallo
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('La descripción es requerida');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 7. TEST 7: Registro con Descripción Corta (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroDescripcionCorta()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'descripcion'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'descripcion');

        // Retorna el mensaje de error correspondiente solo para 'descripcion'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'descripcion' ? 'La descripción debe tener al menos 10 caracteres' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['descripcion' => 'La descripción debe tener al menos 10 caracteres']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Corta', // Descripción para provocar el fallo
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('La descripción debe tener al menos 10 caracteres');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 8. TEST 8: Registro con Descripción Larga (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroDescripcionLarga()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'descripcion'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'descripcion');

        // Retorna el mensaje de error correspondiente solo para 'descripcion'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'descripcion' ? 'La descripción no puede superar los 1000 caracteres' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['descripcion' => 'La descripción no puede superar los 1000 caracteres']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => str_repeat('A', 1001), // Descripción larga para provocar el fallo
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('La descripción no puede superar los 1000 caracteres');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 9. TEST 9: Registro con Precio Vacio (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroPrecioVacio()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'precio'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'precio');

        // Retorna el mensaje de error correspondiente solo para 'precio'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'precio' ? 'El precio es requerido' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['precio' => 'El precio es requerido']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '', // Precio vacío para provocar el fallo
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El precio es requerido');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 10. TEST 10: Registro con Precio no decimal (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroPrecioNoDecimal()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'precio'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'precio');

        // Retorna el mensaje de error correspondiente solo para 'precio'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'precio' ? 'El precio debe ser un número decimal' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['precio' => 'El precio debe ser un número decimal']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 'Ab', // Precio no decimal para provocar el fallo
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El precio debe ser un número decimal');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 11. TEST 11: Registro con Precio cero (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroPrecioCero()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'precio'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'precio');

        // Retorna el mensaje de error correspondiente solo para 'precio'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'precio' ? 'El precio debe ser mayor a 0' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['precio' => 'El precio debe ser mayor a 0']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '0', // Precio cero para provocar el fallo
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El precio debe ser mayor a 0');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 12. TEST 12: Registro con Stock vacio (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroStockVacio()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'stock'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'stock');

        // Retorna el mensaje de error correspondiente solo para 'stock'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'stock' ? 'El stock es requerido' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['stock' => 'El stock es requerido']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '', // Stock vacío para provocar el fallo
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El stock es requerido');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 13. TEST 13: Registro con Stock negativo (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroStockNegativo()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'stock'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'stock');

        // Retorna el mensaje de error correspondiente solo para 'stock'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'stock' ? 'El stock no puede ser negativo' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['stock' => 'El stock no puede ser negativo']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '-5', // Stock negativo para provocar el fallo
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El stock no puede ser negativo');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 14. TEST 14: Registro con Stock decimal (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroStockDecimal()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'stock'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'stock');

        // Retorna el mensaje de error correspondiente solo para 'stock'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'stock' ? 'El stock debe ser un número entero' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['stock' => 'El stock debe ser un número entero']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '3.5', // Stock decimal para provocar el fallo
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('El stock debe ser un número entero');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 15. TEST 15: Registro con Fecha de Edición Invalida (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroFechaEdicionInvalida()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'fechaedicion'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'fechaedicion');

        // Retorna el mensaje de error correspondiente solo para 'fechaedicion'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'fechaedicion' ? 'La fecha de edición no puede ser anterior a 1750' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['fechaedicion' => 'La fecha de edición no puede ser anterior a 1750']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '25',
            'fechaedicion' => 1700, // Fecha de edición inválida para provocar el fallo
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('La fecha de edición no puede ser anterior a 1750');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 16. TEST 16: Registro con Fecha de Edición Futura (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroFechaEdicionFutura()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'fechaedicion'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'fechaedicion');

        // Retorna el mensaje de error correspondiente solo para 'fechaedicion'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'fechaedicion' ? 'La fecha de edición no puede ser en el futuro' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['fechaedicion' => 'La fecha de edición no puede ser en el futuro']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '25',
            'fechaedicion' => 2028, // Fecha de edición futura para provocar el fallo
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('La fecha de edición no puede ser en el futuro');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 17. TEST 17: Registro sin imagen (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroSinImagen()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'imagen'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'imagen');

        // Retorna el mensaje de error correspondiente solo para 'imagen'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'imagen' ? 'Seleccione una imagen' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['imagen' => 'Seleccione una imagen']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', ''])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '25',
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('Seleccione una imagen');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 18. TEST 18: Registro Archivo Pdf (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroArchivoPdf()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'imagen'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'imagen');

        // Retorna el mensaje de error correspondiente solo para 'imagen'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'imagen' ? 'Solo se permiten imágenes JPG, PNG o WEBP' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['imagen' => 'Solo se permiten imágenes JPG, PNG o WEBP']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Etiqueta Test']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'imagen.pdf'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '25',
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('Solo se permiten imágenes JPG, PNG o WEBP');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }

    // =========================================================================
    // 19. TEST 19: Registro Imagen con tamaño excesivo (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroImagenTamañoExcesivo()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'imagen'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'imagen');

        // Retorna el mensaje de error correspondiente solo para 'imagen'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'imagen' ? 'La imagen no debe superar los 4 MB' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['imagen' => 'La imagen no debe superar los 4 MB']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Ninguna']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'imagen.png'])
            ->getMock();

        // Simulamos que el método getSize() del archivo retorna 5 MB
        $mockFile->method('getSize')->willReturn(5242880);


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '25',
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('La imagen no debe superar los 4 MB');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 20. TEST 20: Registro Categoria sin seleccionar (Validación Fallida)
    // =========================================================================
    public function testRegistrarLibroCategoriaSinSeleccionar()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(false); // Simula validación fallida

        // Retorna true solo si el campo consultado es 'categoria'
        $mockValidation->method('hasError')
            ->willReturnCallback(fn($field) => $field === 'categoria');

        // Retorna el mensaje de error correspondiente solo para 'categoria'
        $mockValidation->method('getError')
            ->willReturnCallback(fn($field) => $field === 'categoria' ? 'Debe seleccionar una categoría' : '');

        $mockValidation->method('getErrors')
            ->willReturn(['categoria' => 'Debe seleccionar una categoría']);

        \Config\Services::injectMock('validation', $mockValidation);

        // 2. MOCK DEL MODELO DE LIBROS
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Al fallar la validación, insert() NUNCA debe ejecutarse
        $mockLibroModel->expects($this->never())->method('insert');

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );

        // 3. MOCK DE LOS MODELOS RELACIONADOS (Evita consultas a la DB real)
        $mockCategoriaModel = $this->getMockBuilder(\App\Models\categorias_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(\App\Models\autores_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(\App\Models\etiqueta_model::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Ninguna']]);
        \CodeIgniter\Config\Factories::injectMock('models', \App\Models\etiqueta_model::class, $mockEtiquetaModel);


        // 4. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'imagen.png'])
            ->getMock();


        // 5. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson', 
            'autor'        => '1', 
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => '18500.50',
            'stock'        => '25',
            'fechaedicion' => 2005,
            'categoria'    => '' // Categoría sin seleccionar para provocar el fallo
        ];

        // 6. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );

        // 7. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');

        // 8. VERIFICACIONES
        // Verificamos que no es una redirección
        $this->assertFalse($resultado->isRedirect());

        // Verificamos que se renderizó con éxito (HTTP Status 200)
        $resultado->assertOK();

        // Verificamos que la vista muestra el error de validación simulado
        $resultado->assertSee('Debe seleccionar una categoría');

        // Verificamos que contenga el título de sección
        $resultado->assertSee('Registro de Libros');
    }


    // =========================================================================
    // 21. TEST 21: Error de Base de Datos y Rollback de Transacción
    // =========================================================================
    public function testRegistrarLibroErrorBaseDatos()
    {
        // 1. MOCK DEL SERVICIO DE VALIDACIÓN (Éxito)
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockValidation->method('withRequest')
            ->willReturn($mockValidation);

        $mockValidation->method('run')
            ->willReturn(true); // Validación pasa para que intente insertar

        \Config\Services::injectMock('validation', $mockValidation);


        // ==========================================================
        // 2. MOCK DEL MODELO QUE FALLA (Lanza Excepción)
        // ==========================================================
        $mockLibroModel = $this->getMockBuilder(\App\Models\libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Simulamos un fallo de base de datos lanzando una excepción
        $mockLibroModel->method('insert')
            ->willThrowException(new \Exception('Error simulado de base de datos'));

        \CodeIgniter\Config\Factories::injectMock(
            'models',
            \App\Models\libros_model::class,
            $mockLibroModel
        );


        // 3. MOCK DEL ARCHIVO SUBIDO
        $mockFile = $this->getMockBuilder(\CodeIgniter\HTTP\Files\UploadedFile::class)
            ->setConstructorArgs(['/tmp/php_upload_test', 'test_image.jpg'])
            ->getMock();

        $mockFile->method('isValid')->willReturn(true);
        $mockFile->method('hasMoved')->willReturn(false);
        $mockFile->method('getRandomName')->willReturn('test_image.jpg');
        $mockFile->method('move')->willReturn(true);


        // 4. DATOS DEL FORMULARIO
        $postData = [
            'titulo'       => 'Percy Jackson',
            'autor'        => 1,
            'descripcion'  => 'Descripción larga de prueba para el libro',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1
        ];


        // 5. MOCK DE LA REQUEST
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([
                config(\Config\App::class),
                $this->uri,
                null,
                new \CodeIgniter\HTTP\UserAgent()
            ])
            ->onlyMethods([
                'getFile',
                'getPost'
            ])
            ->getMock();

        $request->method('getFile')
            ->with('imagen')
            ->willReturn($mockFile);

        $request->method('getPost')
            ->willReturnCallback(
                fn ($campo = null) =>
                    $campo === null
                        ? $postData
                        : ($postData[$campo] ?? null)
            );


        // 6. EJECUTAR EL CONTROLADOR
        $resultado = $this->withRequest($request)
            ->controller(\App\Controllers\LibroController::class)
            ->execute('registrar_libro');


        // ==========================================================
        // 7. VERIFICACIONES
        // ==========================================================
        // Verificamos que sí hubo redirección
        $this->assertTrue($resultado->isRedirect());

        // Verificamos que redirige a "gestionar"
        $resultado->assertRedirectTo(base_url('gestionar'));

        // Verificamos el mensaje en el flashdata con la clave 'error'
        $this->assertEquals(
            'Ocurrió un error al registrar el libro: Error simulado de base de datos',
            session()->getFlashdata('error') 
        );
    }
}