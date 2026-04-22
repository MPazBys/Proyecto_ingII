<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\libros_model;
use App\Models\categorias_model;
use App\Models\etiqueta_model;
use App\Models\autores_model;

class LibroController extends BaseController {

    public function form_agregar_libro() {
        $categoria = new categorias_model();
        $autor = new autores_model();
        $data['categorias'] = $categoria->findAll();
        $data['autores'] = $autor->findAll();
        $data['titulo'] = 'Agregar libro';
        return view('plantilla/nav_admin_view', $data).
            view('backend/agregar_libro').
            view('plantilla/footer_admin_view');
    }

    public function registrar_libro() {
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();

        $validation->setRules([
            'titulo' => 'required|min_length[3]|max_length[100]',
            'autor' => 'required|is_not_unique[autores.idAutor]',
            'descripcion' => 'required|min_length[10]|max_length[1000]',
            'precio' => 'required|decimal|greater_than[0]',
            'stock' => 'required|integer|greater_than_equal_to[0]',
            'imagen' => 'uploaded[imagen]|is_image[imagen]|max_size[imagen,4096]|mime_in[imagen,image/jpeg,image/png,image/webp]',
            'categoria' => 'required|is_not_unique[categorias.idCategoria]',

        ], [
            'titulo' => [
                'required' => 'El título es obligatorio',
                'min_length' => 'El título debe tener al menos 3 caracteres',
                'max_length' => 'El título no puede superar los 100 caracteres'
            ],
            'autor' => [
                'required' => 'Debe seleccionar un autor',
                'is_not_unique' => 'El autor seleccionado no es válido'
            ],
            'descripcion' => [
                'required' => 'La descripción es obligatoria',
                'min_length' => 'La descripción debe tener al menos 10 caracteres',
                'max_length' => 'La descripción no puede superar los 1000 caracteres'
            ],
            'precio' => [
                'required' => 'El precio es obligatorio',
                'decimal' => 'El precio debe ser un número decimal',
                'greater_than' => 'El precio debe ser mayor a 0'
            ],
            'stock' => [
                'required' => 'El stock es obligatorio',
                'integer' => 'El stock debe ser un número entero',
                'greater_than_equal_to' => 'El stock no puede ser negativo'
            ],
            'imagen' => [
                'uploaded' => 'Seleccione una imagen',
                'is_image' => 'El archivo debe ser una imagen válida',
                'max_size' => 'La imagen no debe superar los 4 MB',
                'mime_in' => 'Solo se permiten imágenes JPG, PNG o WEBP'
            ],
            'categoria' => [
                'required' => 'Debe seleccionar una categoría',
                'is_not_unique' => 'La categoría seleccionada no es válida'
            ]
        ]);

        if($validation->withRequest($request)->run()) {
            $img = $this->request->getFile('imagen');
            $nombre_aleatorio = $img->getRandomName();
            $img->move(ROOTPATH.'assets/upload', $nombre_aleatorio);

            $data = [
                'nombreLibro' => $request->getPost('titulo'),
                'idAutor' => $request->getPost('autor'),
                'descripcionLibro' => $request->getPost('descripcion'),
                'precioLibro' => $request->getPost('precio'),
                'stockLibro' => $request->getPost('stock'),
                'imagenLibro' => $nombre_aleatorio,
                'idCategoria' => $request->getPost('categoria'),
                'estado' => 1,
                'etiquetaLibro' => 1
            ];

            $libro = new libros_model();
            $libro->insert($data);
            return redirect()->route('gestionar')->with('mensaje', 'El libro se registro correctamente!');
        } else {
            $categoria = new categorias_model();
            $data['validation'] = $validation;

            $data['categorias'] = $categoria->findAll();
            
            $autor = new autores_model();
            $data['autores'] = $autor->findAll();

            $data['titulo'] = 'Agregar libro';

            return view('plantilla/nav_admin_view', $data).
            view('backend/agregar_libro').
            view('plantilla/footer_admin_view');
        }
    }

    function gestionar_libros() {
        $libro_Model = new libros_model();
        $categoria = new categorias_model();
        $etiquetas = new etiqueta_model();
        $autor = new autores_model();

        $data['categorias'] = $categoria->findAll();
        $data['etiquetas'] = $etiquetas->findAll();
        $data['autores'] = $autor->findAll();

        $data['libro'] = $libro_Model->select('libros.*, categorias.nombreCategoria, etiqueta.nombre as nombreEtiqueta, 
        autores.nombreAutor')
            ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
            ->join('etiqueta', 'etiqueta.idEtiqueta = libros.etiquetaLibro')
            ->join('autores', 'autores.idAutor = libros.idAutor')
            ->findAll();

        $data['titulo'] = 'Listar libro';

        return view('plantilla/nav_admin_view', $data). 
            view('backend/listar_libros'). 
            view('plantilla/footer_admin_view');
    }


    function editar_libro($id=null) {
        $libro_Model = new libros_model();
        $categoria = new categorias_model();
        $etiquetas = new etiqueta_model();
        $autor = new autores_model();

        $data['categorias'] = $categoria->findAll();
        $data['etiquetas'] = $etiquetas->findAll();
        $data['autores'] = $autor->findAll();
        $data['libro'] = $libro_Model->where('idLibro', $id)->first();
        $data['titulo'] = 'Editar libro';

        return view('plantilla/nav_admin_view', $data).
            view('backend/editar_libro').
            view('plantilla/footer_admin_view');
    }

    function actualizar_libro() {
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $img = $request->getFile('imagen');

        $reglas = [
            'titulo' => 'required|min_length[3]|max_length[100]',
            'autor' => 'required|is_not_unique[autores.idAutor]',
            'descripcion' => 'required|min_length[10]|max_length[1000]',
            'precio' => 'required|decimal|greater_than[0]',
            'stock' => 'required|integer|greater_than_equal_to[0]',
            'categoria' => 'required|is_not_unique[categorias.idCategoria]',
            'etiqueta' => 'required|is_not_unique[etiqueta.idEtiqueta]'
        ];

        if ($img && $img->isValid() && !$img->hasMoved()) {
            $reglas['imagen'] = 'is_image[imagen]|max_size[imagen,4096]|mime_in[imagen,image/jpeg,image/png,image/webp]';
        }

        $mensajes = [
            'titulo' => [
                'required' => 'El título es obligatorio',
                'min_length' => 'El título debe tener al menos 3 caracteres',
                'max_length' => 'El título no puede superar los 100 caracteres'
            ],
            'autor' => [
                'required' => 'Debe seleccionar un autor',
                'is_not_unique' => 'El autor seleccionado no es válido'
            ],
            'descripcion' => [
                'required' => 'La descripción es obligatoria',
                'min_length' => 'La descripción debe tener al menos 10 caracteres',
                'max_length' => 'La descripción no puede superar los 1000 caracteres'
            ],
            'precio' => [
                'required' => 'El precio es obligatorio',
                'decimal' => 'El precio debe ser un número decimal',
                'greater_than' => 'El precio debe ser mayor a 0'
            ],
            'stock' => [
                'required' => 'El stock es obligatorio',
                'integer' => 'El stock debe ser un número entero',
                'greater_than_equal_to' => 'El stock no puede ser negativo'
            ],
            'imagen' => [
                'is_image' => 'El archivo debe ser una imagen válida',
                'max_size' => 'La imagen no debe superar los 4 MB',
                'mime_in' => 'Solo se permiten imágenes JPG, PNG o WEBP'
            ],
            'categoria' => [
                'required' => 'Debe seleccionar una categoría',
                'is_not_unique' => 'La categoría seleccionada no es válida'
            ],
            'etiqueta' => [
                'required' => 'Debe seleccionar una etiqueta',
                'is_not_unique' => 'La etiqueta seleccionada no es válida'
            ]
        ];

        $validation->setRules($reglas, $mensajes);
        
        $id = $request->getPost('id');

        if($validation->withRequest($request)->run()) {
            $libro = new libros_model();
            $libro_actual = $libro->find($id);

            // Si hay nueva imagen, se guardarla; si no, se mantiene la existente
            $nombre_aleatorio = $libro_actual['imagenLibro'];
            if($img && $img->isValid() && !$img->hasMoved()) {
                $nombre_aleatorio = $img->getRandomName();
                $img->move(ROOTPATH . 'assets/upload', $nombre_aleatorio);
            }

            $data = [
                'nombreLibro' => $request->getPost('titulo'),
                'idLibro' => $request->getPost('autor'),
                'descripcionLibro' => $request->getPost('descripcion'),
                'precioLibro' => $request->getPost('precio'),
                'stockLibro' => $request->getPost('stock'),
                'imagenLibro' => $nombre_aleatorio,
                'idCategoria' => $request->getPost('categoria'),
                'etiquetaLibro' => $request->getPost('etiqueta')
            ];

            $libro->update($id, $data);
            return redirect()->route('gestionar')->with('mensaje', 'El libro se modifico correctamente!');
            
        } else {
            $categoria = new categorias_model();
            $libro_Model = new libros_model();
            $etiquetas = new etiqueta_model();
            $autor = new autores_model();
            $data['categorias'] = $categoria->findAll();
            $data['etiquetas'] = $etiquetas->findAll();
            $data['autores'] = $autor->findAll();
            $data['libro'] = $libro_Model->find($id);
            $data['validation'] = $validation;
            $data['titulo'] = 'Editar libro';

            return view('plantilla/nav_admin_view', $data).
                view('backend/editar_libro').
                view('plantilla/footer_admin_view');
        }
    }

    public function eliminar_libro($id=null) {
        $data = array('estado'=>'0');
        $libro = new libros_model();
        $libro->update($id, $data);
        return redirect()->route('gestionar');
    }

    public function activar_libro($id=null) {
        $data = array('estado'=>'1');
        $libro = new libros_model();
        $libro->update($id, $data);
        return redirect()->route('gestionar');
    }

    public function listar_libros() {
    $libro_Model = new \App\Models\libros_model();
    $categoriaModel = new \App\Models\categorias_model();

    // Recibimos los parámetros del formulario
    $filtro_por = $this->request->getGet('filtro_por'); // 'nombre', 'autor' o 'genero'
    $clave = $this->request->getGet('clave');

    $data['categorias'] = $categoriaModel->findAll();

    $builder = $libro_Model->select('libros.*, categorias.nombreCategoria, autores.nombreAutor')
        ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
        ->join('autores', 'autores.idAutor = libros.idAutor')
        ->where('libros.estado', 1)
        ->where('libros.stockLibro >', 0);

    // Lógica de filtrado dinámico
    if (!empty($filtro_por) && !empty($clave)) {
        switch ($filtro_por) {
            case 'nombre':
                $builder->like('libros.nombreLibro', $clave);
                break;
            case 'autor':
                $builder->like('autores.nombreAutor', $clave);
                break;
            case 'genero':
                $builder->like('categorias.nombreCategoria', $clave);
                break;
        }
    }

    $data['libro'] = $builder->findAll();
    $data['titulo'] = 'Catálogo de Libros';

    // Para mantener los valores en el formulario después de buscar
    $data['filtrado'] = ['tipo' => $filtro_por, 'clave' => $clave];

    return view('plantilla/header_view', $data).
           view('plantilla/nav_view').
           view('contenido/catalogo').
           view('plantilla/footer_view');
}

    public function buscar() {
        $busqueda = $this->request->getGet('busqueda');
        $libroModel = new libros_model();
        $categoriaModel = new categorias_model();
        $autorModel = new autores_model();

        // Buscar por título, autor o categoría
        $libros = $libroModel
            ->select('libros.*, categorias.nombreCategoria as nombreCategoria, autores.nombreAutor as nombreAutor',)
            ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
            ->join('autores', 'autores.idAutor = libros.idAutor')
            ->groupStart()
                ->like('libros.nombreLibro', $busqueda)
                ->orLike('autores.nombreAutor', $busqueda)
                ->orLike('categorias.nombreCategoria', $busqueda)
            ->groupEnd()
            ->findAll();

        // Traer todas las categorías para el filtro de la vista
        $data['categorias'] = $categoriaModel->findAll();
        $data['autores'] = $autorModel->findAll();
        $data['libro'] = $libros;
        $data['titulo'] = 'Resultados de búsqueda: ' . esc($busqueda);

        return view('plantilla/header_view', $data). 
            view('plantilla/nav_view').
            view('contenido/catalogo').
            view('plantilla/footer_view');
    }

    public function buscar_admin() {
        $filtro_por = $this->request->getGet('filtro_por');
        $busqueda = $this->request->getGet('busqueda');
        $libroModel = new libros_model();
        
        // Traemos los modelos necesarios para los labels de la vista
        $categoriaModel = new categorias_model();
        $etiquetas = new etiqueta_model();
        $data['categorias'] = $categoriaModel->findAll();
        $data['etiquetas'] = $etiquetas->findAll();

        $builder = $libroModel->select('libros.*, categorias.nombreCategoria, etiqueta.nombre as nombreEtiqueta, autores.nombreAutor')
            ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
            ->join('etiqueta', 'etiqueta.idEtiqueta = libros.etiquetaLibro')
            ->join('autores', 'autores.idAutor = libros.idAutor');

        // Filtro estricto
        if (!empty($filtro_por) && !empty($busqueda)) {
            if ($filtro_por == 'nombre') {
                $builder->like('libros.nombreLibro', $busqueda);
            } elseif ($filtro_por == 'autor') {
                $builder->like('autores.nombreAutor', $busqueda);
            } elseif ($filtro_por == 'genero') {
                $builder->like('categorias.nombreCategoria', $busqueda);
            }
        }

        $data['libro'] = $builder->findAll();
        $data['titulo'] = 'Listado de Libros';

        return view('plantilla/nav_admin_view', $data). 
            view('backend/listar_libros'). 
            view('plantilla/footer_admin_view');
    }


    public function listar_libros_admin() {
        $libro_Model = new libros_model();
        $categoriaModel = new categorias_model();
        $etiquetas = new etiqueta_model();
        $autorModel = new autores_model();

        $data['etiquetas'] = $etiquetas->findAll();
        $data['categorias'] = $categoriaModel->findAll();
        $data['autores'] = $autorModel->findAll();

        $categoriaSeleccionada = $this->request->getGet('categoria');
        $autorSeleccionado = $this->request->getGet('autor');

        $builder = $libro_Model
            ->select('libros.*, categorias.nombreCategoria as nombreCategoria, etiqueta.nombre as nombreEtiqueta
            autores.nombreAutor as nombreAutor',)
            ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
            ->join('etiqueta', 'etiqueta.idEtiqueta = libros.etiquetaLibro')
            ->join('autores', 'autores.idAutor = libros.idAutor')
            ->where('libros.estado', 1)
            ->where('libros.stockLibro >', 0);

        if (!empty($categoriaSeleccionada)) {
            $builder->where('libros.idCategoria', $categoriaSeleccionada);
            $builder->where('libros.idAutor', $autorSeleccionado);
        }

        $data['libro'] = $builder->findAll();
        $data['titulo'] = 'Listar libro';

        return view('plantilla/nav_admin_view', $data). 
            view('backend/listar_libros'). 
            view('plantilla/footer_admin_view');
    }

    public function index()
    {
        $libro_Model = new libros_model();
        $categoria = new categorias_model();
        $autor = new autores_model();
        $data['categorias'] = $categoria->findAll();
        $data['autores'] = $autor->findAll();
        $data['libro'] = $libro_Model->join('categorias', 'categorias.idCategoria = libros.idCategoria')
        ->join('autores', 'autores.idAutor = libros.idAutor')->findAll();
        $data['titulo'] = 'Productos';

        return view('plantilla/nav_admin_view', $data).
        view('backend/productos').
        view('plantilla/footer_admin_view');
    }

    function inicio(){
        $libro_Model = new libros_model();

        $data['libro'] = $libro_Model->where('estado', 1)->where('stockLibro >', 0)
        ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
        ->join('etiqueta', 'etiqueta.idEtiqueta = libros.etiquetaLibro')
        ->join('autores', 'autores.idAutor = libros.idAutor')->findAll();

        $data['titulo'] = 'Bienvenidos';

        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/inicio').
            view('plantilla/footer_view');
    }
}