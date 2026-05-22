<?php

namespace App\Models;

use CodeIgniter\Model;

class persona_model extends Model
{
    protected $table      = 'persona';
    protected $primaryKey = 'idPersona';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['nombrePersona', 'apellidoPersona', 'correoPersona', 'contrasenia', 'idEstado', 'idPerfil', 'dni', 'idDireccion'];

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

    // Reglas de validación para el registro de clientes (formulario externo)
    public array $registroRules = [
        'nombre'           => 'required|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/]|min_length[2]|max_length[50]',
        'apellido'         => 'required|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/]|min_length[2]|max_length[50]',
        'correo'           => 'required|valid_email|is_unique[persona.correoPersona]|max_length[100]',
        'password'         => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).+$/]',
        'confirm_password' => 'required|matches[password]',
    ];

    // Mensajes de error personalizados para la validación de registro de clientes
    public array $registroErrors = [
        'nombre' => [
            'required'    => 'El nombre es obligatorio',
            'regex_match' => 'Solo se permiten letras (incluyendo ñ y acentos) y espacios',
            'min_length'  => 'Debe tener al menos 2 caracteres',
            'max_length'  => 'No puede superar los 50 caracteres',
        ],
        'apellido' => [
            'required'    => 'El apellido es obligatorio',
            'regex_match' => 'Solo se permiten letras (incluyendo ñ y acentos) y espacios',
            'min_length'  => 'Debe tener al menos 2 caracteres',
            'max_length'  => 'No puede superar los 50 caracteres',
        ],
        'correo' => [
            'required'    => 'El correo es obligatorio',
            'valid_email' => 'Debe ser un correo válido',
            'is_unique'   => 'El cliente ya se encuentra registrado',
            'max_length'  => 'El correo no puede superar los 100 caracteres',
        ],
        'password' => [
            'required'    => 'Ingresar la contraseña es obligatorio',
            'min_length'  => 'La contraseña debe tener como mínimo 8 caracteres',
            'regex_match' => 'Debe incluir mayúsculas, minúsculas, números y un carácter especial',
        ],
        'confirm_password' => [
            'required'    => 'Repetir la contraseña es obligatorio',
            'matches'     => 'Las contraseñas no coinciden',
        ]
    ];

    // Reglas de validación para el inicio de sesión de usuarios
    public array $loginRules = [
        'correo'   => 'required|valid_email|max_length[100]',
        'password' => 'required|min_length[8]',
    ];

    // Mensajes de error personalizados para el inicio de sesión
    public array $loginErrors = [
        'correo' => [
            'required'    => 'El correo es obligatorio',
            'valid_email' => 'Debe ser un correo válido',
            'max_length'  => 'El correo no puede superar los 100 caracteres'
        ],
        'password' => [
            'required'    => 'Ingresar la contraseña es obligatorio',
            'min_length'  => 'La contraseña debe tener como mínimo 8 caracteres',
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

    public function verificarCredenciales(string $correo, string $password): ?array
    {
        $user = $this->where('correoPersona', $correo)
                     ->where('idEstado', 1)
                     ->first();

        if ($user && password_verify($password, $user['contrasenia'])) {
            return [
                'id'       => $user['idPersona'],
                'nombre'   => $user['nombrePersona'],
                'apellido' => $user['apellidoPersona'],
                'correo'   => $user['correoPersona'],
                'perfil'   => $user['idPerfil'],
                'login'    => true
            ];
        }

        return null;
    }
}