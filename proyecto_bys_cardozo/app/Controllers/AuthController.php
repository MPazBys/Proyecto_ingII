<?php

namespace App\Controllers;

use App\Models\persona_model;

class AuthController extends BaseController
{
    // Reglas de validación para el registro de clientes (formulario externo)
    protected array $registroRules = [
        'nombre'           => 'required|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/]|min_length[2]|max_length[50]',
        'apellido'         => 'required|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/]|min_length[2]|max_length[50]',
        'correo'           => 'required|valid_email|is_unique[persona.correoPersona]|max_length[100]',
        'password'         => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).+$/]',
        'confirm_password' => 'required|matches[password]',
    ];

    // Mensajes de error personalizados para la validación de registro de clientes
    protected array $registroErrors = [
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
    protected array $loginRules = [
        'correo'   => 'required|valid_email|max_length[100]',
        'password' => 'required|min_length[8]',
    ];

    // Mensajes de error personalizados para el inicio de sesión
    protected array $loginErrors = [
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

    /**
     * Registra un nuevo cliente en el sistema después de validar los campos del formulario
     */
    public function add_cliente()
    {
        $userRegister = new persona_model();

        // Validar el formulario de registro usando las reglas y errores definidos en el controlador
        if (!$this->validate($this->registroRules, $this->registroErrors)) {
            $data['titulo'] = 'Registrarse';
            $data['validation'] = $this->validator->getErrors();
            return view('contenido/registro', $data);
        }

        $request = \Config\Services::request();
        
        // Formatear nombre y apellido con la primera letra de cada palabra en mayúscula (ej. juan carlos -> Juan Carlos)
        $nombreFormateado = mb_convert_case(trim($request->getPost('nombre')), MB_CASE_TITLE, "UTF-8");
        $apellidoFormateado = mb_convert_case(trim($request->getPost('apellido')), MB_CASE_TITLE, "UTF-8");

        $data = [
            'nombrePersona'   => $nombreFormateado,
            'apellidoPersona' => $apellidoFormateado,
            'correoPersona'   => $request->getPost('correo'),
            'contrasenia'     => password_hash($request->getPost('password'), PASSWORD_BCRYPT),
            'idPerfil'        => 2,
            'idEstado'        => 1
        ];

        $userRegister->insert($data);

        return redirect()->route('login')->with('mensaje', 'Su registro se realizo exitosamente!');
    }

    /**
     * Busca y valida un usuario para iniciar sesión en el sistema
     */
    public function buscar_usuario()
    {
        $userModel = new persona_model();

        // Validar las credenciales de inicio de sesión usando las reglas y errores del controlador
        if (!$this->validate($this->loginRules, $this->loginErrors)) {
            $data['titulo'] = 'Login';
            $data['validation'] = $this->validator;
            return view('contenido/login', $data);
        }

        $request = \Config\Services::request();
        $mail = $request->getPost('correo');
        $pass = $request->getPost('password');
        $sessionData = $this->verificarCredenciales($mail, $pass);

        if ($sessionData) {
            session()->set($sessionData);
            if ($sessionData['perfil'] === '1') {
                return redirect()->route('user_admin');
            } else {
                return redirect()->route('/');
            }
        }

        $data['titulo'] = 'Login';
        $data['validation'] = $this->validator;
        $data['error_login'] = 'Usuario y/o contraseña incorrectos';

        return view('contenido/login', $data);
    }

    public function cerrar_sesion()
    {
        $session = session();
        $session->destroy();
        return redirect()->route('login');
    }

    private function verificarCredenciales(string $correo, string $password): ?array
    {
        $userModel = new persona_model();
        $user = $userModel->where('correoPersona', $correo)
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
