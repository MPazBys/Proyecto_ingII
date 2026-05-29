<?php

namespace App\Controllers;

// Importación de los modelos necesarios para el catálogo y sus relaciones
use App\Models\libros_model;
use App\Models\categorias_model;
use App\Models\etiqueta_model;
use App\Models\autores_model;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controlador principal para la gestión de libros.
 * Centraliza y administra el catálogo público, el motor de búsquedas global
 * y las operaciones CRUD internas del panel de administración.
 */
class LibroController extends BaseController 
{
    /**
     * @var string Ruta física absoluta para el almacenamiento de las portadas subidas.
     * Mantiene la barra final para asegurar consistencia al usar unlink() o file_exists().
     */
    private const UPLOAD_PATH = ROOTPATH . 'assets/upload/';

    /**
     * Propiedades de la clase tipadas estrictamente (PHP 7.4+).
     * Proporcionan autocompletado completo en el IDE y previenen errores de tipado.
     */
    protected libros_model $libroModel;
    protected categorias_model $categoriaModel;
    protected autores_model $autorModel;
    protected etiqueta_model $etiquetaModel;

    /**
     * Constructor del controlador.
     * Carga e instancia los modelos una sola vez mediante el helper 'model()' de CI4,
     * el cual aplica el patrón Singleton por defecto para optimizar el consumo de memoria RAM.
     */
    public function __construct() 
    {
        $this->libroModel      = model(libros_model::class);
        $this->categoriaModel  = model(categorias_model::class);
        $this->autorModel      = model(autores_model::class);
        $this->etiquetaModel   = model(etiqueta_model::class);
    }

    // --- MÉTODOS DE RENDERIZADO AUXILIAR (Bajo Acoplamiento / DRY) ---

    /**
     * Combina y renderiza las vistas estructuradas del panel de administración.
     */
    private function renderAdmin(string $viewName, array $data = []): string 
    {
        return view('plantilla/nav_admin_view', $data) .
               view($viewName, $data) .
               view('plantilla/footer_admin_view');
    }

    /**
     * Combina y renderiza las vistas estándar para el catálogo público de clientes.
     */
    private function renderPublic(string $viewName, array $data = []): string 
    {
        return view('plantilla/header_view', $data) .
               view('plantilla/nav_view', $data) .
               view($viewName, $data) .
               view('plantilla/footer_view', $data);
    }

    /**
     * Recupera los registros de soporte de la DB para rellenar los combos desplegables select del formulario.
     */
    private function cargarDatosFormulario(string $titulo): array 
    {
        return [
            'categorias' => $this->categoriaModel->findAll(),
            'autores'    => $this->autorModel->findAll(),
            'etiquetas'  => $this->etiquetaModel->findAll(),
            'titulo'     => $titulo
        ];
    }

    /**
     * Sanitiza y mapea las entradas POST recibidas del cliente hacia los nombres reales
     * de las columnas de la tabla 'libros' en la base de datos.
     */
    private function mapearDatosRequest(string $nombreImagen, ?int $idEtiqueta = null): array
    {
        return [
            'nombreLibro'      => $this->request->getPost('titulo'),
            'idAutor'          => $this->request->getPost('autor'),
            'descripcionLibro' => $this->request->getPost('descripcion'),
            'precioLibro'      => $this->request->getPost('precio'),
            'stockLibro'       => $this->request->getPost('stock'),
            'fechaEdicion'     => $this->request->getPost('fechaedicion'),
            'imagenLibro'      => $nombreImagen,
            'idCategoria'      => $this->request->getPost('categoria'),
            'idEtiqueta'       => $idEtiqueta ?? 1 // Valor por defecto para inserciones
        ];
    }

    /**
     * Helper para inyectar datos de combos y el catálogo completo sin restricciones al backend de gestión.
     */
    private function prepararDatosListadoAdmin(string $titulo): array
    {
        $data = $this->cargarDatosFormulario($titulo);
        $data['libro'] = $this->getLibrosConRelaciones(); // El administrador visualiza TODO (incluso pausados o sin stock)
        return $data;
    }

    // --- VALIDACIÓN DE FORMULARIOS ---

    /**
     * Corre las validaciones de servidor mapeando inputs POST contra reglas de negocio complejas.
     * Soporta flujos alternativos y dinámicos para operaciones de Registro o Edición.
     */
    private function validarFormulario(bool $esEdicion = false): bool 
    {
        $validation = \Config\Services::validation();

        // Reglas generales aplicables a altas y modificaciones
        $reglas = [
            'titulo'       => 'required|min_length[3]|max_length[100]',
            'autor'        => 'required|is_not_unique[autores.idAutor]', // Valida existencia de FK
            'descripcion'  => 'required|min_length[10]|max_length[1000]',
            'precio'       => 'required|decimal|greater_than[0]',
            'stock'        => 'required|integer|greater_than_equal_to[0]',
            // Valida formato de año (4 dígitos) acotado entre el año 1750 y el año corriente dinámico
            'fechaedicion' => 'required|regex_match[/^[0-9]{4}$/]|greater_than_equal_to[1750]|less_than_equal_to[' . date('Y') . ']',
            'categoria'    => 'required|is_not_unique[categorias.idCategoria]', // Valida existencia de FK
        ];

        // Regla obligatoria únicamente durante el flujo de modificación
        if ($esEdicion) {
            $reglas['etiqueta'] = 'required|is_not_unique[etiqueta.idEtiqueta]';
        }

        // Recuperar y validar metadatos del archivo de imagen subido
        $img = $this->request->getFile('imagen');
        if (!$esEdicion || ($img && $img->isValid() && !$img->hasMoved())) {
            $reglaImagen = $esEdicion ? '' : 'uploaded[imagen]|';
            $reglaImagen .= 'is_image[imagen]|max_size[imagen,4096]|mime_in[imagen,image/jpeg,image/png,image/webp]';
            $reglas['imagen'] = $reglaImagen;
        }

        // Localización de los mensajes de error informativos en castellano
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
     * Enrutador para vistas administrativas redundantes (Lista de productos / Listado de control interno).
     * Reutiliza lógica cargando parámetros estructurales diferenciados por string de entrada.
     */
    public function index(string $vista = 'listar_libros'): string 
    {
        $titulo = ($vista === 'productos') ? 'Productos' : 'Listar libro';
        $data = $this->prepararDatosListadoAdmin($titulo);
        $viewPath = ($vista === 'productos') ? 'backend/productos' : 'backend/listar_libros';

        return $this->renderAdmin($viewPath, $data);
    }

    /**
     * Formulario unificado para operaciones de Alta y Modificación.
     * Si recibe un ID entero por parámetro muta a modo Modificación e intenta precargar los datos del libro.
     */
    public function formulario(?int $id = null): string|RedirectResponse
    {
        $titulo = $id ? 'Editar libro' : 'Agregar libro';
        $data = $this->cargarDatosFormulario($titulo);
        $viewPath = 'backend/agregar_libro';

        // Lógica de bifurcación condicional si el contexto detecta una edición
        if ($id !== null) {
            $data['libro'] = $this->libroModel->find($id);
            // Clausura de escape inmediato si intentan forzar la edición de un ID inexistente
            if (!$data['libro']) {
                return redirect()->route('gestionar')->with('error', 'El libro no existe.');
            }
            $viewPath = 'backend/editar_libro';
        } else {
            $data['libro'] = null; // Instancia vacía para inicializar campos limpios en el Alta
        }

        return $this->renderAdmin($viewPath, $data);
    }

    /**
     * Procesa la inserción atómica de un nuevo libro.
     * Utiliza transacciones de Base de Datos (ACID) para sincronizar de forma segura 
     * el registro SQL con el almacenamiento físico del archivo en disco.
     */
    public function registrar_libro(): RedirectResponse|string 
    {
        // Principio "Early Return": Si la validación falla, rompe el flujo e interrumpe la ejecución del método
        if (!$this->validarFormulario(false)) {
            $data = $this->cargarDatosFormulario('Agregar libro');
            $data['validation'] = \Config\Services::validation();
            return $this->renderAdmin('backend/agregar_libro', $data);
        }

        $img = $this->request->getFile('imagen');
        $nombre_aleatorio = $img->getRandomName(); // Genera hash seguro para prevenir colisiones de nombres

        $data = $this->mapearDatosRequest($nombre_aleatorio);
        $data['estado'] = 1; // El libro inicia activo lógicamente por defecto

        $db = \Config\Database::connect();
        $db->transBegin(); // Apertura de transacción

        try {
            // Paso 1: Intentar escritura en la DB relacional
            $this->libroModel->insert($data);
            // Paso 2: Si el motor de base de datos no falló, persistir el archivo físico en disco
            $img->move(self::UPLOAD_PATH, $nombre_aleatorio);
            
            $db->transCommit(); // Transacción consolidada con éxito
            return redirect()->route('gestionar')->with('mensaje', '¡El libro se registró correctamente!');
        } catch (\Exception $e) {
            $db->transRollback(); // Reversión completa ante fallos para evitar archivos huérfanos o basura SQL
            return redirect()->route('gestionar')->with('error', 'Ocurrió un error al registrar el libro: ' . $e->getMessage());
        }
    }

    /**
     * Procesa las actualizaciones de datos en el catálogo.
     * Controla de manera transaccional el borrado del archivo de imagen anterior 
     * únicamente si la nueva portada se escribe correctamente.
     */
    public function actualizar_libro(): RedirectResponse|string 
    {
        $id = (int)$this->request->getPost('id');

        // Early Return ante fallos de validación en los inputs del formulario
        if (!$this->validarFormulario(true)) {
            $data = $this->cargarDatosFormulario('Editar libro');
            $data['libro'] = $this->libroModel->find($id);
            $data['validation'] = \Config\Services::validation();
            return $this->renderAdmin('backend/editar_libro', $data);
        }

        $libro_actual = $this->libroModel->find($id);
        if (!$libro_actual) {
            return redirect()->route('gestionar')->with('error', 'El libro no existe.');
        }

        $img = $this->request->getFile('imagen');
        $imagenAntigua = $libro_actual['imagenLibro'];
        $imagenNueva = null;
        $nombreImagenFinal = $imagenAntigua; // Conserva el nombre actual si no deciden reemplazar la portada

        // Procesamiento condicional si el usuario subió una portada de reemplazo
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $imagenNueva = $img->getRandomName();
            $nombreImagenFinal = $imagenNueva;
        }

        $data = $this->mapearDatosRequest($nombreImagenFinal, (int)$this->request->getPost('etiqueta'));

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Paso 1: Actualizar registro SQL de la tabla 'libros'
            $this->libroModel->update($id, $data);

            // Paso 2: Si hay nueva imagen, moverla físicamente y limpiar el disco del archivo obsoleto
            if ($imagenNueva) {
                $img->move(self::UPLOAD_PATH, $imagenNueva);
                if (!empty($imagenAntigua) && file_exists(self::UPLOAD_PATH . $imagenAntigua)) {
                    unlink(self::UPLOAD_PATH . $imagenAntigua); // Liberación segura de almacenamiento físico
                }
            }

            $db->transCommit();
            return redirect()->route('gestionar')->with('mensaje', '¡El libro se modificó correctamente!');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->route('gestionar')->with('error', 'Ocurrió un error al actualizar el libro: ' . $e->getMessage());
        }
    }

    /**
     * Cambia el estado binario lógico del libro.
     * Unifica los antiguos flujos redundantes de Alta lógica (estado = 1) y Baja lógica (estado = 0).
     */
    public function cambiar_estado(int $id, int $estado): RedirectResponse
    {
        $this->libroModel->update($id, ['estado' => (string)$estado]);
        return redirect()->route('gestionar');
    }

    // --- ACCIONES DE CATÁLOGO PÚBLICO Y BÚSQUEDAS ---

    /**
     * Controlador de contexto para listado y filtrado general de catálogos.
     * Conmuta dinámicamente el renderizado y las restricciones de consultas
     * dependiendo de si el usuario logueado posee un token administrativo en la sesión.
     */
    public function listar(): string 
    {
        $esAdmin = session()->get('perfil') == 1; // Evaluación de bandera de contexto (Perfil Administrativo)

        // Subflujo A: El usuario logueado posee privilegios de Administrador
        if ($esAdmin) {
            $data = $this->cargarDatosFormulario('Listar libro');
            $data['libro'] = $this->getLibrosConRelaciones([
                'idCategoria' => $this->request->getGet('categoria'),
                'idAutor'     => $this->request->getGet('autor')
            ]); 

            return $this->renderAdmin('backend/listar_libros', $data);
        }

        // Subflujo B: Navegación de cliente/público general
        $filtro_por = $this->request->getGet('filtro_por');
        $clave = $this->request->getGet('clave');

        $data['categorias'] = $this->categoriaModel->findAll();
        $data['titulo']     = 'Catálogo de Libros';
        $data['filtrado']   = ['tipo' => $filtro_por, 'clave' => $clave];

        // Restricción rigurosa: El público únicamente visualiza ítems activos con stock físico real en góndola
        $data['libro'] = $this->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true,
            'filtro_por'  => $filtro_por,
            'clave'       => $clave
        ]);

        return $this->renderPublic('contenido/catalogo', $data);
    }

    /**
     * Motor de búsqueda integrado.
     * Unifica las búsquedas por columnas de backend y las búsquedas globales por string de frontend
     * conmutando comportamiento vía flags de sesión.
     */
    public function buscar(): string 
    {
        $esAdmin = session()->get('perfil') == 1;
        $busqueda = $this->request->getGet('busqueda');

        // Subflujo A: Búsqueda indexada por columnas internas en backend administrativo
        if ($esAdmin) {
            $data = $this->cargarDatosFormulario('Listado de Libros');
            $data['libro'] = $this->getLibrosConRelaciones([
                'filtro_por' => $this->request->getGet('filtro_por'),
                'clave'      => $busqueda
            ]);

            return $this->renderAdmin('backend/listar_libros', $data);
        }

        // Subflujo B: Búsqueda global difusa (Fuzzy Search) orientada al cliente general público
        $data['categorias'] = $this->categoriaModel->findAll();
        $data['autores']    = $this->autorModel->findAll();
        $data['libro']      = $this->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true,
            'busqueda'    => $busqueda
        ]);
        $data['titulo']     = 'Resultados de búsqueda: ' . esc($busqueda);

        return $this->renderPublic('contenido/catalogo', $data);
    }

    /**
     * Carga el escaparate público de bienvenida inyectando únicamente recomendaciones vigentes.
     */
    public function inicio(): string 
    {
        $data['libro'] = $this->getLibrosConRelaciones([
            'soloActivos' => true,
            'conStock'    => true
        ]);
        $data['titulo'] = 'Bienvenidos';

        return $this->renderPublic('contenido/inicio', $data);
    }

    // --- QUERY BUILDER CENTRALIZADO (Abstracción de Datos / DRY) ---

    /**
     * Único constructor relacional SQL de la entidad de libros en todo el controlador.
     * Mapea joins y concatena cláusulas WHERE/LIKE dinámicamente procesando un array asociativo de parámetros.
     */
    private function getLibrosConRelaciones(array $filtros = []): array
    {
        // Construcción y definición de la proyección base de la consulta e inicialización de Joins
        $builder = $this->libroModel->select('libros.*, categorias.nombreCategoria, etiqueta.nombre as nombreEtiqueta, autores.nombreAutor, autores.apellidoAutor, CONCAT(autores.nombreAutor, " ", autores.apellidoAutor) AS autor_formateado')
            ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
            ->join('etiqueta', 'etiqueta.idEtiqueta = libros.idEtiqueta')
            ->join('autores', 'autores.idAutor = libros.idAutor');

        // Filtro condicional: Excluir bajas lógicas
        if (!empty($filtros['soloActivos'])) {
            $builder->where('libros.estado', 1);
        }

        // Filtro condicional: Excluir quiebres de inventario (Stock = 0)
        if (!empty($filtros['conStock'])) {
            $builder->where('libros.stockLibro >', 0);
        }

        // Filtro condicional: Restringir por categoría específica
        if (!empty($filtros['idCategoria'])) {
            $builder->where('libros.idCategoria', $filtros['idCategoria']);
        }

        // Filtro condicional: Restringir por clave foránea de autor
        if (!empty($filtros['idAutor'])) {
            $builder->where('libros.idAutor', $filtros['idAutor']);
        }

        // Filtro condicional de frontend: Agrupa compuertas OR anidadas (Fuzzy/Global Like)
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $builder->groupStart()
                ->like('libros.nombreLibro', $busqueda)
                ->orLike('autores.nombreAutor', $busqueda)
                ->orLike('categorias.nombreCategoria', $busqueda)
            ->groupEnd();
        }

        // Filtro condicional de backend: Discriminador selectivo basado en columnas específicas de la tabla
        if (!empty($filtros['filtro_por']) && !empty($filtros['clave'])) {
            switch ($filtros['filtro_por']) {
                case 'nombre':
                    $builder->like('libros.nombreLibro', $filtros['clave']);
                    break;
                case 'autor':
                    $builder->like('autores.nombreAutor', $filtros['clave']);
                    break;
                case 'genero':
                    $builder->like('categorias.nombreCategoria', $filtros['clave']);
                    break;
            }
        }

        // Ejecución física de la consulta y retorno de colecciones estructuradas de datos
        return $builder->findAll();
    }
}