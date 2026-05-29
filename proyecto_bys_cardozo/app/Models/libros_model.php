<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar la tabla de libros.
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
    protected $validationRules = [];

    // Mensajes de error personalizados para la validación
    protected $validationMessages = [];

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


}