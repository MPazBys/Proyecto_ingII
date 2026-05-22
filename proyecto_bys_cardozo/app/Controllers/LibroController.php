<?php

namespace App\Controllers;

use App\Models\libros_model;
use App\Models\categorias_model;
use App\Models\etiqueta_model;
use App\Models\autores_model;

/**
 * Controlador principal para la gestión de libros.
 * Administra el catálogo público, las búsquedas y las operaciones CRUD administrativas.
 */
class LibroController extends BaseController 
{
    // Propiedades para almacenar las instancias de los modelos (Inyección de Dependencias)
    protected $libroModel;
    protected $categoriaModel;
    protected $autorModel;
    protected $etiquetaModel;

    public function __construct() 
    {
        // Cargar instancias de modelos una sola vez en el constructor
        $this->libroModel = model(libros_model::class);
        $this->categoriaModel = model(categorias_model::class);
        $this->autorModel = model(autores_model::class);
        $this->etiquetaModel = model(etiqueta_model::class);
    }

    // --- MÉTODOS DE RENDERIZADO AUXILIAR (Bajo Acoplamiento / DRY) ---

    /**
     * Helper para renderizar vistas administrativas con cabecera y pie de página de admin.
     */
    private function renderAdmin(string $viewName, array $data = []) 
    {
        return view('plantilla/nav_admin_view', $data) .
               view($viewName, $data) .
               view('plantilla/footer_admin_view');
    }

    /**
     * Helper para renderizar vistas públicas con cabecera, navegación y pie de página estándar.
     */
    private function renderPublic(string $viewName, array $data = []) 
    {
        return view('plantilla/header_view', $data) .
               view('plantilla/nav_view', $data) .
               view($viewName, $data) .
               view('plantilla/footer_view', $data);
    }

    /**
     * Helper para cargar las opciones que rellenan los combos desplegables en formularios.
     */
    private function cargarDatosFormulario(string $titulo) 
    {
        return [
            'categorias' => $this->categoriaModel->findAll(),
            'autores'    => $this->autorModel->findAll(),
            'etiquetas'  => $this->etiquetaModel->findAll(),
            'titulo'     => $titulo
        ];
    }

    // --- VALIDACIÓN DE FORMULARIOS ---

    /**
     * Valida los datos recibidos por POST mapeando las reglas a los inputs del formulario.
     * Soporta validaciones condicionales tanto para registro como edición.
     */
    private function validarFormulario($esEdicion = false) 
    {
        $validation = \Config\Services::validation();

        // Reglas de validación aplicadas sobre los nombres de campos del formulario
        $reglas = [
            'titulo'      => 'required|min_length[3]|max_length[100]',
            'autor'       => 'required|is_not_unique[autores.idAutor]',
            'descripcion' => 'required|min_length[10]|max_length[1000]',
            'precio'      => 'required|decimal|greater_than[0]',
            'stock'       => 'required|integer|greater_than_equal_to[0]',
            'fechaedicion'=> 'required|regex_match[/^[0-9]{4}$/]|greater_than_equal_to[1750]|less_than_equal_to[' . date('Y') . ']',
            'categoria'   => 'required|is_not_unique[categorias.idCategoria]',
        ];

        // Regla requerida solo en la edición
        if ($esEdicion) {
            $reglas['etiqueta'] = 'required|is_not_unique[etiqueta.idEtiqueta]';
        }

        // Validación dinámica del archivo de imagen
        $img = $this->request->getFile('imagen');
        if (!$esEdicion || ($img && $img->isValid() && !$img->hasMoved())) {
            $reglaImagen = $esEdicion ? '' : 'uploaded[imagen]|';
            $reglaImagen .= 'is_image[imagen]|max_size[imagen,4096]|mime_in[imagen,image/jpeg,image/png,image/webp]';
            $reglas['imagen'] = $reglaImagen;
        }

        // Mensajes de error en español para mostrar al usuario en las vistas
        $mensajes = [
            'titulo' => [
                'required'   => 'El título es obligatorio',
                'min_length' => 'El título debe tener al menos 3 caracteres',
                'max_length' => 'El título no puede superar los 100 caracteres'
            ],
            'autor' => [
                'required'      => 'Debe seleccionar un autor',
                'is_not_unique' => 'El autor seleccionado no es válido'
            ],
            'descripcion' => [
                'required'   => 'La descripción es obligatoria',
                'min_length' => 'La descripción debe tener al menos 10 caracteres',
                'max_length' => 'La descripción no puede superar los 1000 caracteres'
            ],
            'precio' => [
                'required'     => 'El precio es obligatorio',
                'decimal'      => 'El precio debe ser un número decimal',
                'greater_than' => 'El precio debe ser mayor a 0'
            ],
            'stock' => [
                'required'              => 'El stock es obligatorio',
                'integer'               => 'El stock debe ser un número entero',
                'greater_than_equal_to' => 'El stock no puede ser negativo'
            ],
            'fechaedicion' => [
                'required'              => 'La fecha de edición es obligatoria',
                'regex_match'           => 'La fecha de edición debe ser un año válido (4 dígitos)',
                'greater_than_equal_to' => 'La fecha de edición no puede ser anterior a 1750',
                'less_than_equal_to'    => 'La fecha de edición no puede ser en el futuro'
            ],
            'imagen' => [
                'uploaded' => 'Seleccione una imagen',
                'is_image' => 'El archivo debe ser una imagen válida',
                'max_size' => 'La imagen no debe superar los 4 MB',
                'mime_in'  => 'Solo se permiten imágenes JPG, PNG o WEBP'
            ],
            'categoria' => [
                'required'      => 'Debe seleccionar una categoría',
                'is_not_unique' => 'La categoría seleccionada no es válida'
            ],
            'etiqueta' => [
                'required'      => 'Debe seleccionar una etiqueta',
                'is_not_unique' => 'La etiqueta seleccionada no es válida'
            ]
        ];

        $validation->setRules($reglas, $mensajes);
        return $validation->withRequest($this->request)->run();
    }

    // --- ACCIONES CRUD ADMINISTRATIVAS ---

    /**
     * Muestra el formulario para registrar un nuevo libro.
     */
    public function form_agregar_libro() 
    {
        $data = $this->cargarDatosFormulario('Agregar libro');
        return $this->renderAdmin('backend/agregar_libro', $data);
    }

    /**
     * Procesa el formulario de registro de un libro en la DB.
     * Utiliza una transacción para asegurar que la imagen física sólo se guarde
     * si la consulta SQL de inserción se realiza de forma exitosa.
     */
    public function registrar_libro() 
    {
        if ($this->validarFormulario(false)) {
            $img = $this->request->getFile('imagen');
            $nombre_aleatorio = $img->getRandomName();

            // Mapeo de datos del post a las columnas reales del modelo libros_model
            $data = [
                'nombreLibro'      => $this->request->getPost('titulo'),
                'idAutor'          => $this->request->getPost('autor'),
                'descripcionLibro' => $this->request->getPost('descripcion'),
                'precioLibro'      => $this->request->getPost('precio'),
                'stockLibro'       => $this->request->getPost('stock'),
                'fechaEdicion'     => $this->request->getPost('fechaedicion'), // Corregido: Columna correcta
                'imagenLibro'      => $nombre_aleatorio,
                'idCategoria'      => $this->request->getPost('categoria'),
                'estado'           => 1,
                'idEtiqueta'       => 1 // Corregido: Columna correcta (por defecto Ninguna en registros nuevos)
            ];

            // Iniciar Transacción de Base de Datos para asegurar atomicidad
            $db = \Config\Database::connect();
            $db->transBegin();

            try {
                // 1. Insertar el registro en la base de datos primero
                $this->libroModel->insert($data);
                
                // 2. Si la inserción SQL fue exitosa, mover físicamente la imagen al directorio de cargas
                $img->move(ROOTPATH . 'assets/upload', $nombre_aleatorio);

                $db->transCommit();
                return redirect()->route('gestionar')->with('mensaje', '¡El libro se registró correctamente!');
            } catch (\Exception $e) {
                // Revertir base de datos y evitar archivos huérfanos si ocurre algún fallo
                $db->transRollback();
                return redirect()->route('gestionar')->with('error', 'Ocurrió un error al registrar el libro: ' . $e->getMessage());
            }
        } else {
            // Recargar vista con errores de validación
            $data = $this->cargarDatosFormulario('Agregar libro');
            $data['validation'] = \Config\Services::validation();

            return $this->renderAdmin('backend/agregar_libro', $data);
        }
    }

    /**
     * Muestra la tabla de gestión interna de libros con todas sus relaciones cargadas.
     */
    public function gestionar_libros() 
    {
        $data = $this->cargarDatosFormulario('Listar libro');
        // Usar la consulta unificada del modelo
        $data['libro'] = $this->libroModel->getLibrosConRelaciones();

        return $this->renderAdmin('backend/listar_libros', $data);
    }

    /**
     * Carga el formulario de edición con los datos del libro seleccionado.
     */
    public function editar_libro($id = null) 
    {
        $data = $this->cargarDatosFormulario('Editar libro');
        $data['libro'] = $this->libroModel->find($id);

        if (!$data['libro']) {
            return redirect()->route('gestionar')->with('error', 'El libro no existe.');
        }

        return $this->renderAdmin('backend/editar_libro', $data);
    }

    /**
     * Actualiza los datos del libro.
     * En caso de subir una nueva imagen, se guarda en el servidor y se borra la anterior
     * únicamente si la consulta de base de datos finaliza de forma exitosa.
     */
    public function actualizar_libro() 
    {
        if ($this->validarFormulario(true)) {
            $id = $this->request->getPost('id');
            $libro_actual = $this->libroModel->find($id);

            if (!$libro_actual) {
                return redirect()->route('gestionar')->with('error', 'El libro no existe.');
            }

            $img = $this->request->getFile('imagen');
            $imagenAntigua = $libro_actual['imagenLibro'];
            $imagenNueva = null;
            $nombreImagenFinal = $imagenAntigua;

            // Generar nuevo nombre aleatorio de imagen si fue cargada
            if ($img && $img->isValid() && !$img->hasMoved()) {
                $imagenNueva = $img->getRandomName();
                $nombreImagenFinal = $imagenNueva;
            }

            // Datos a actualizar mapeados a las columnas reales en la DB
            $data = [
                'nombreLibro'      => $this->request->getPost('titulo'),
                'idAutor'          => $this->request->getPost('autor'),
                'descripcionLibro' => $this->request->getPost('descripcion'),
                'precioLibro'      => $this->request->getPost('precio'),
                'stockLibro'       => $this->request->getPost('stock'),
                'fechaEdicion'     => $this->request->getPost('fechaedicion'), // Agregado: Actualización de la fecha de edición
                'imagenLibro'      => $nombreImagenFinal,
                'idCategoria'      => $this->request->getPost('categoria'),
                'idEtiqueta'       => $this->request->getPost('etiqueta') // Corregido: Columna correcta
            ];

            // Iniciar Transacción de Base de Datos para asegurar atomicidad
            $db = \Config\Database::connect();
            $db->transBegin();

            try {
                // 1. Modificar base de datos primero
                $this->libroModel->update($id, $data);

                // 2. Si es exitosa, gestionar la subida física y borrar el archivo anterior
                if ($imagenNueva) {
                    $img->move(ROOTPATH . 'assets/upload', $imagenNueva);
                    
                    // Borrar el archivo viejo del almacenamiento
                    if (!empty($imagenAntigua) && file_exists(ROOTPATH . 'assets/upload/' . $imagenAntigua)) {
                        unlink(ROOTPATH . 'assets/upload/' . $imagenAntigua);
                    }
                }

                $db->transCommit();
                return redirect()->route('gestionar')->with('mensaje', '¡El libro se modificó correctamente!');
            } catch (\Exception $e) {
                // Revertir actualización si ocurre un fallo en escritura o archivos
                $db->transRollback();
                return redirect()->route('gestionar')->with('error', 'Ocurrió un error al actualizar el libro: ' . $e->getMessage());
            }
        } else {
            // Volver a cargar el formulario con errores si la validación falla
            $id = $this->request->getPost('id');
            $data = $this->cargarDatosFormulario('Editar libro');
            $data['libro'] = $this->libroModel->find($id);
            $data['validation'] = \Config\Services::validation();

            return $this->renderAdmin('backend/editar_libro', $data);
        }
    }

    /**
     * Realiza una baja lógica (desactivación) estableciendo estado = 0.
     */
    public function eliminar_libro($id = null) 
    {
        $this->libroModel->update($id, ['estado' => '0']);
        return redirect()->route('gestionar');
    }

    /**
     * Activa un libro estableciendo estado = 1.
     */
    public function activar_libro($id = null) 
    {
        $this->libroModel->update($id, ['estado' => '1']);
        return redirect()->route('gestionar');
    }

    /**
     * Muestra la lista de productos disponibles en el panel de administrador.
     */
    public function index() 
    {
        $data = $this->cargarDatosFormulario('Productos');
        $data['libro'] = $this->libroModel->getLibrosConRelaciones();

        return $this->renderAdmin('backend/productos', $data);
    }

    // --- ACCIONES DE CATÁLOGO PÚBLICO Y BÚSQUEDAS ---

    /**
     * Muestra la lista pública de libros aplicando filtros condicionales de catálogo.
     */
    public function listar_libros() 
    {
        $filtro_por = $this->request->getGet('filtro_por');
        $clave = $this->request->getGet('clave');

        $data['categorias'] = $this->categoriaModel->findAll();
        $data['titulo'] = 'Catálogo de Libros';
        $data['filtrado'] = ['tipo' => $filtro_por, 'clave' => $clave];

        // Obtener solo libros activos y con stock usando la consulta unificada
        $data['libro'] = $this->libroModel->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true,
            'filtro_por'  => $filtro_por,
            'clave'       => $clave
        ]);

        return $this->renderPublic('contenido/catalogo', $data);
    }

    /**
     * Procesa la barra de búsqueda global del catálogo público.
     */
    public function buscar() 
    {
        $busqueda = $this->request->getGet('busqueda');
        
        $data['categorias'] = $this->categoriaModel->findAll();
        $data['autores']    = $this->autorModel->findAll();
        $data['libro']      = $this->libroModel->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true,
            'busqueda'    => $busqueda
        ]);
        $data['titulo']     = 'Resultados de búsqueda: ' . esc($busqueda);

        return $this->renderPublic('contenido/catalogo', $data);
    }

    /**
     * Procesa búsquedas de administrador por columnas en la tabla de listado interno.
     */
    public function buscar_admin() 
    {
        $filtro_por = $this->request->getGet('filtro_por');
        $busqueda = $this->request->getGet('busqueda');

        $data = $this->cargarDatosFormulario('Listado de Libros');
        $data['libro'] = $this->libroModel->getLibrosConRelaciones([
            'filtro_por' => $filtro_por,
            'clave'      => $busqueda
        ]);

        return $this->renderAdmin('backend/listar_libros', $data);
    }

    /**
     * Lista y filtra libros en el panel administrativo basándose en categoría y autor.
     */
    public function listar_libros_admin() 
    {
        $categoriaSeleccionada = $this->request->getGet('categoria');
        $autorSeleccionado = $this->request->getGet('autor');

        $data = $this->cargarDatosFormulario('Listar libro');
        
        // Obtener usando relaciones correctas
        $data['libro'] = $this->libroModel->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true,
            'idCategoria' => $categoriaSeleccionada,
            'idAutor'     => $autorSeleccionado
        ]);

        return $this->renderAdmin('backend/listar_libros', $data);
    }

    /**
     * Carga los productos recomendados en la pantalla de bienvenida del catálogo general público.
     */
    public function inicio() 
    {
        $data['libro'] = $this->libroModel->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true
        ]);
        $data['titulo'] = 'Bienvenidos';

        return $this->renderPublic('contenido/inicio', $data);
    }
}