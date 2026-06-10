<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Controllers\LibroController;
use App\Models\libros_model;
use App\Models\categorias_model;
use App\Models\autores_model;
use App\Models\etiqueta_model;

final class ActualizarLibroTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Factories::reset();
    }

    /**
     * Helper centralizado para preparar mocks e invocar actualizar_libro.
     */
    private function ejecutarPruebaActualizar(
        array $postData,
        ?array $fileConfig = null,
        ?array $validationErrors = null,
        bool $dbFails = false,
        bool $findReturnsNull = false
    ) {
        // 1. Mock de Validación
        $mockValidation = $this->getMockBuilder(\CodeIgniter\Validation\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockValidation->method('withRequest')->willReturn($mockValidation);

        if ($validationErrors === null) {
            $mockValidation->method('run')->willReturn(true);
        } else {
            $mockValidation->method('run')->willReturn(false);
            $mockValidation->method('hasError')
                ->willReturnCallback(fn($field) => isset($validationErrors[$field]));
            $mockValidation->method('getError')
                ->willReturnCallback(fn($field) => $validationErrors[$field] ?? '');
            $mockValidation->method('getErrors')
                ->willReturn($validationErrors);
        }
        \Config\Services::injectMock('validation', $mockValidation);

        // 2. Mock de Modelos Relacionados (Para cargarDatosFormulario si hay fallo)
        $mockCategoriaModel = $this->getMockBuilder(categorias_model::class)->disableOriginalConstructor()->getMock();
        $mockCategoriaModel->method('findAll')->willReturn([['idCategoria' => 1, 'nombreCategoria' => 'Fantasía']]);
        Factories::injectMock('models', categorias_model::class, $mockCategoriaModel);

        $mockAutorModel = $this->getMockBuilder(autores_model::class)->disableOriginalConstructor()->getMock();
        $mockAutorModel->method('findAll')->willReturn([['idAutor' => 1, 'nombreAutor' => 'Rick', 'apellidoAutor' => 'Riordan']]);
        Factories::injectMock('models', autores_model::class, $mockAutorModel);

        $mockEtiquetaModel = $this->getMockBuilder(etiqueta_model::class)->disableOriginalConstructor()->getMock();
        $mockEtiquetaModel->method('findAll')->willReturn([['idEtiqueta' => 1, 'nombre' => 'Ninguna']]);
        Factories::injectMock('models', etiqueta_model::class, $mockEtiquetaModel);

        // 3. Mock de Modelo Principal (libros_model)
        $mockLibroModel = $this->getMockBuilder(libros_model::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($findReturnsNull) {
            $mockLibroModel->method('find')->willReturn(null);
        } else {
            $mockLibroModel->method('find')->willReturn([
                'idLibro'          => 5,
                'nombreLibro'      => 'Percy Jackson',
                'idAutor'          => 1,
                'descripcionLibro' => 'Descripción del libro antiguo',
                'precioLibro'      => 18500.50,
                'stockLibro'       => 25,
                'fechaEdicion'     => 2005,
                'imagenLibro'      => 'foto_antigua.jpg',
                'idCategoria'      => 1,
                'idEtiqueta'       => 1
            ]);
        }

        if ($dbFails) {
            $mockLibroModel->method('update')->willThrowException(new \Exception('Error de base de datos simulado'));
        } else {
            $mockLibroModel->method('update')->willReturn(true);
        }
        Factories::injectMock('models', libros_model::class, $mockLibroModel);

        // 4. Mock de Archivo Subido
        $mockFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$fileConfig['tmp_path'] ?? '/tmp/php_upload_test', $fileConfig['name'] ?? ''])
            ->getMock();

        if (!empty($fileConfig['name'])) {
            $mockFile->method('isValid')->willReturn($fileConfig['isValid'] ?? true);
            $mockFile->method('hasMoved')->willReturn(false);
            $mockFile->method('getRandomName')->willReturn($fileConfig['random_name'] ?? 'nueva_foto.jpg');
            $mockFile->method('move')->willReturn(true);
        } else {
            $mockFile->method('isValid')->willReturn(false);
        }

        // 5. Mock de Request
        $request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->setConstructorArgs([config(\Config\App::class), $this->uri, null, new \CodeIgniter\HTTP\UserAgent()])
            ->onlyMethods(['getFile', 'getPost'])
            ->getMock();

        $request->method('getFile')->with('imagen')->willReturn($mockFile);
        $request->method('getPost')->willReturnCallback(
            fn ($campo = null) => $campo === null ? $postData : ($postData[$campo] ?? null)
        );

        // 6. Ejecutar método
        return $this->withRequest($request)
            ->controller(LibroController::class)
            ->execute('actualizar_libro');
    }

    // =========================================================================
    // FILA 2: Campos completos y válidos -> Libro modificado correctamente
    // =========================================================================
    public function testActualizarLibroCamposCompletosValidos()
    {
        $postData = [
            'id'           => 5,
            'titulo'       => 'Percy Jackson',
            'autor'        => 1,
            'descripcion'  => 'Descripción válida con más de 10 caracteres',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1,
            'etiqueta'     => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gestionar'));
        $this->assertEquals('¡El libro se modificó correctamente!', session()->getFlashdata('mensaje'));
    }

    // =========================================================================
    // FILA 3: ID inexistente -> Error de sistema
    // =========================================================================
    public function testActualizarLibroIdInexistente()
    {
        $postData = [
            'id'           => 999, // No existe en base de datos
            'titulo'       => 'Percy Jackson',
            'autor'        => 1,
            'descripcion'  => 'Descripción válida del libro.',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1,
            'etiqueta'     => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, null, false, true);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gestionar'));
        $this->assertEquals('El libro no existe.', session()->getFlashdata('error'));
    }

    // =========================================================================
    // FILA 4: Título vacío -> Error de validación
    // =========================================================================
    public function testActualizarLibroTituloVacio()
    {
        $postData = [
            'id' => 5, 'titulo' => '', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['titulo' => 'El título es obligatorio']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El título es obligatorio');
    }

    // =========================================================================
    // FILA 5: Título con menos de 3 caracteres -> Error de validación
    // =========================================================================
    public function testActualizarLibroTituloMenosDe3Caracteres()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Ab', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['titulo' => 'El título debe tener al menos 3 caracteres']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El título debe tener al menos 3 caracteres');
    }

    // =========================================================================
    // FILA 6: Título demasiado largo (>100 caracteres) -> Error de validación
    // =========================================================================
    public function testActualizarLibroTituloDemasiadoLargo()
    {
        $postData = [
            'id' => 5, 'titulo' => str_repeat('A', 101), 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['titulo' => 'El título no puede superar los 100 caracteres']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El título no puede superar los 100 caracteres');
    }

    // =========================================================================
    // FILA 7: Autor no seleccionado -> Error de validación
    // =========================================================================
    public function testActualizarLibroAutorNoSeleccionado()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => '', 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['autor' => 'Debe seleccionar un autor']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('Debe seleccionar un autor');
    }

    // =========================================================================
    // FILA 8: Descripción vacía -> Error de validación
    // =========================================================================
    public function testActualizarLibroDescripcionVacia()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => '', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['descripcion' => 'La descripción es obligatoria']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('La descripción es obligatoria');
    }

    // =========================================================================
    // FILA 9: Descripción demasiado corta (<10 caracteres) -> Error de validación
    // =========================================================================
    public function testActualizarLibroDescripcionDemasiadoCorta()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Corta', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['descripcion' => 'La descripción debe tener al menos 10 caracteres']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('La descripción debe tener al menos 10 caracteres');
    }

    // =========================================================================
    // FILA 10: Descripción demasiado larga (>1000 caracteres) -> Error de validación
    // =========================================================================
    public function testActualizarLibroDescripcionDemasiadoLarga()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => str_repeat('D', 1001), 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['descripcion' => 'La descripción no puede superar los 1000 caracteres']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('La descripción no puede superar los 1000 caracteres');
    }

    // =========================================================================
    // FILA 11: Precio vacío -> Error de validación
    // =========================================================================
    public function testActualizarLibroPrecioVacio()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => '', 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['precio' => 'El precio es obligatorio']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El precio es obligatorio');
    }

    // =========================================================================
    // FILA 12: Precio no decimal -> Error de validación
    // =========================================================================
    public function testActualizarLibroPrecioNoDecimal()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 'Ab', 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['precio' => 'El precio debe ser un número decimal']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El precio debe ser un número decimal');
    }

    // =========================================================================
    // FILA 13: Precio cero -> Error de validación
    // =========================================================================
    public function testActualizarLibroPrecioCero()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 0, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['precio' => 'El precio debe ser mayor a 0']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El precio debe ser mayor a 0');
    }

    // =========================================================================
    // FILA 14: Cantidad vacía -> Error de validación
    // =========================================================================
    public function testActualizarLibroCantidadVacia()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => '', 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['stock' => 'El stock es obligatorio']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El stock es obligatorio');
    }

    // =========================================================================
    // FILA 15: Cantidad negativa -> Error de validación
    // =========================================================================
    public function testActualizarLibroCantidadNegativa()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => -5, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['stock' => 'El stock no puede ser negativo']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El stock no puede ser negativo');
    }

    // =========================================================================
    // FILA 16: Cantidad decimal -> Error de validación
    // =========================================================================
    public function testActualizarLibroCantidadDecimal()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 3.5, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['stock' => 'El stock debe ser un número entero']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('El stock debe ser un número entero');
    }

    // =========================================================================
    // FILA 17: Año no permitido (<1750) -> Error de validación
    // =========================================================================
    public function testActualizarLibroAnioNoPermitido()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 1700, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['fechaedicion' => 'La fecha de edición no puede ser anterior a 1750']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('La fecha de edición no puede ser anterior a 1750');
    }

    // =========================================================================
    // FILA 18: Año futuro -> Error de validación
    // =========================================================================
    public function testActualizarLibroAnioFuturo()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2028, 'categoria' => 1, 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['fechaedicion' => 'La fecha de edición no puede ser en el futuro']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('La fecha de edición no puede ser en el futuro');
    }

    // =========================================================================
    // FILA 19: Imagen con formato no permitido (PDF) -> Error de validación
    // =========================================================================
    public function testActualizarLibroImagenFormatoNoPermitido()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];
        $fileConfig = ['name' => 'archivo.pdf', 'isValid' => false];

        $resultado = $this->ejecutarPruebaActualizar($postData, $fileConfig, ['imagen' => 'Solo se permiten imágenes JPG, PNG o WEBP']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('Solo se permiten imágenes JPG, PNG o WEBP');
    }

    // =========================================================================
    // FILA 20: Imagen demasiado pesada (>4MB) -> Error de validación
    // =========================================================================
    public function testActualizarLibroImagenDemasiadoPesada()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => 1
        ];
        $fileConfig = ['name' => 'imagen_pesada.jpg', 'isValid' => false];

        $resultado = $this->ejecutarPruebaActualizar($postData, $fileConfig, ['imagen' => 'La imagen no debe superar los 4 MB']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('La imagen no debe superar los 4 MB');
    }

    // =========================================================================
    // FILA 21: Categoría no seleccionada -> Error de validación
    // =========================================================================
    public function testActualizarLibroCategoriaNoSeleccionada()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => '', 'etiqueta' => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['categoria' => 'Debe seleccionar una categoría']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('Debe seleccionar una categoría');
    }

    // =========================================================================
    // FILA 22: Etiqueta no seleccionada -> Error de validación
    // =========================================================================
    public function testActualizarLibroEtiquetaNoSeleccionada()
    {
        $postData = [
            'id' => 5, 'titulo' => 'Percy Jackson', 'autor' => 1, 'descripcion' => 'Descripción válida', 
            'precio' => 18500.50, 'stock' => 25, 'fechaedicion' => 2005, 'categoria' => 1, 'etiqueta' => ''
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, ['etiqueta' => 'Debe seleccionar una etiqueta']);

        $this->assertFalse($resultado->isRedirect());
        $resultado->assertOK();
        $resultado->assertSee('Debe seleccionar una etiqueta');
    }

    // =========================================================================
    // FILA 23: Falla interna del sistema (DB Exception) -> Error durante la actualización
    // =========================================================================
    public function testActualizarLibroFallaInternaSistema()
    {
        $postData = [
            'id'           => 5,
            'titulo'       => 'Percy Jackson',
            'autor'        => 1,
            'descripcion'  => 'Descripción larga y válida del libro.',
            'precio'       => 18500.50,
            'stock'        => 25,
            'fechaedicion' => 2005,
            'categoria'    => 1,
            'etiqueta'     => 1
        ];

        $resultado = $this->ejecutarPruebaActualizar($postData, null, null, true);

        $this->assertTrue($resultado->isRedirect());
        $resultado->assertRedirectTo(base_url('gestionar'));
        $this->assertEquals(
            'Ocurrió un error al actualizar el libro: Error de base de datos simulado',
            session()->getFlashdata('error')
        );
    }
}