<?php

namespace App\Models;

use CodeIgniter\Model;

class consulta_model extends Model
{
    protected $table      = 'consultas';
    protected $primaryKey = 'idConsulta';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['asunto', 'mensaje', 'respondido', 'idPersona'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Reglas de validación para la creación de una consulta de contacto
    public array $addConsultaRules = [
        'motivo'   => 'required|max_length[100]',
        'consulta' => 'required|max_length[250]|min_length[10]',
    ];

    // Mensajes de error personalizados para la validación de la consulta
    public array $addConsultaErrors = [
        'motivo' => [
            'required'   => 'El motivo es obligatorio', 
            'max_length' => 'El motivo de la consulta debe tener como máximo 100 caracteres',
        ], 
        'consulta' => [
            'required'   => 'La consulta es requerida', 
            'min_length' => 'La consulta debe tener como mínimo 10 caracteres',
            'max_length' => 'La consulta debe tener como máximo 250 caracteres',
        ],
    ];

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

    public function getConsultasConPersona()
    {
        return $this->select('consultas.*, CONCAT(persona.nombrePersona, " ", persona.apellidoPersona) AS nombreApellido, persona.correoPersona AS correo')
                    ->join('persona', 'persona.idPersona = consultas.idPersona')
                    ->findAll();
    }

    public function getConsultaConPersona($idConsulta)
    {
        return $this->select('consultas.*, CONCAT(persona.nombrePersona, " ", persona.apellidoPersona) AS nombreApellido, persona.correoPersona AS correo')
                    ->join('persona', 'persona.idPersona = consultas.idPersona')
                    ->where('consultas.idConsulta', $idConsulta)
                    ->first();
    }
}