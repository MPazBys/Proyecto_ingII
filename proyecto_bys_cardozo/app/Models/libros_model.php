<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar la tabla de libros.
 * Centraliza la validación, seguridad de campos permitidos y consultas relacionales.
 */
class libros_model extends Model
{
    protected $table      = 'libros';
    protected $primaryKey = 'idLibro';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Campos permitidos para inserción/edición masiva (deben coincidir con la DB)
    protected $allowedFields = [
        'nombreLibro', 
        'idCategoria', 
        'precioLibro', 
        'stockLibro', 
        'estado', 
        'descripcionLibro', 
        'imagenLibro', 
        'idEtiqueta', 
        'idAutor', 
        'fechaEdicion'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Fechas
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    // Reglas de validación estáticas para el modelo
    protected $validationRules = [
        'nombreLibro'      => 'required|min_length[3]|max_length[100]',
        'idAutor'          => 'required|is_not_unique[autores.idAutor]',
        'descripcionLibro' => 'required|min_length[10]|max_length[1000]',
        'precioLibro'      => 'required|decimal|greater_than[0]',
        'stockLibro'       => 'required|integer|greater_than_equal_to[0]',
        'fechaEdicion'     => 'required|regex_match[/^[0-9]{4}$/]|greater_than_equal_to[1750]',
        'idCategoria'      => 'required|is_not_unique[categorias.idCategoria]',
    ];

    // Mensajes de error personalizados para la validación
    protected $validationMessages = [
        'nombreLibro' => [
            'required'   => 'El título es obligatorio',
            'min_length' => 'El título debe tener al menos 3 caracteres',
            'max_length' => 'El título no puede superar los 100 caracteres'
        ],
        'idAutor' => [
            'required'      => 'Debe seleccionar un autor',
            'is_not_unique' => 'El autor seleccionado no es válido'
        ],
        'descripcionLibro' => [
            'required'   => 'La descripción es obligatoria',
            'min_length' => 'La descripción debe tener al menos 10 caracteres',
            'max_length' => 'La descripción no puede superar los 1000 caracteres'
        ],
        'precioLibro' => [
            'required'     => 'El precio es obligatorio',
            'decimal'      => 'El precio debe ser un número decimal',
            'greater_than' => 'El precio debe ser mayor a 0'
        ],
        'stockLibro' => [
            'required'              => 'El stock es obligatorio',
            'integer'               => 'El stock debe ser un número entero',
            'greater_than_equal_to' => 'El stock no puede ser negativo'
        ],
        'fechaEdicion' => [
            'required'              => 'La fecha de edición es obligatoria',
            'regex_match'           => 'La fecha de edición debe ser un año válido (4 dígitos)',
            'greater_than_equal_to' => 'La fecha de edición no puede ser anterior a 1750'
        ],
        'idCategoria' => [
            'required'      => 'Debe seleccionar una categoría',
            'is_not_unique' => 'La categoría seleccionada no es válida'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Obtiene los libros combinándolos con sus categorías, etiquetas y autores.
     * Centraliza todas las consultas de listados, búsquedas y filtros en un solo lugar.
     *
     * @param array $filtros Filtros opcionales (soloActivos, conStock, idCategoria, idAutor, busqueda, filtro_por, clave)
     * @return array Listado de libros que cumplen con las condiciones.
     */
    public function getLibrosConRelaciones(array $filtros = [])
    {
        // Construcción básica del Query Builder con sus Joins correspondientes
        $builder = $this->select('libros.*, categorias.nombreCategoria, etiqueta.nombre as nombreEtiqueta, autores.nombreAutor, autores.apellidoAutor, CONCAT(autores.nombreAutor, " ", autores.apellidoAutor) AS autor_formateado')
            ->join('categorias', 'categorias.idCategoria = libros.idCategoria')
            ->join('etiqueta', 'etiqueta.idEtiqueta = libros.idEtiqueta')
            ->join('autores', 'autores.idAutor = libros.idAutor');

        // Filtro: Solo libros habilitados (activos)
        if (!empty($filtros['soloActivos'])) {
            $builder->where('libros.estado', 1);
        }

        // Filtro: Solo libros con disponibilidad (stock mayor a cero)
        if (!empty($filtros['conStock'])) {
            $builder->where('libros.stockLibro >', 0);
        }

        // Filtro: Libros pertenecientes a una categoría específica
        if (!empty($filtros['idCategoria'])) {
            $builder->where('libros.idCategoria', $filtros['idCategoria']);
        }

        // Filtro: Libros escritos por un autor específico
        if (!empty($filtros['idAutor'])) {
            $builder->where('libros.idAutor', $filtros['idAutor']);
        }

        // Filtro: Búsqueda global de texto (título, autor o categoría)
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $builder->groupStart()
                ->like('libros.nombreLibro', $busqueda)
                ->orLike('autores.nombreAutor', $busqueda)
                ->orLike('categorias.nombreCategoria', $busqueda)
            ->groupEnd();
        }

        // Filtro: Búsqueda de administrador por una columna o criterio específico
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

        return $builder->findAll();
    }
}