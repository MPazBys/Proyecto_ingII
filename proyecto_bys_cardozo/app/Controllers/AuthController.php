<?php

namespace App\Controllers;

use App\Models\persona_model;

class AuthController extends BaseController
{
    /**
     * Registra un nuevo cliente en el sistema después de validar los campos del formulario
     */
    public function add_cliente()
    {
        $userRegister = new persona_model();

        // Validar el formulario de registro usando las reglas y errores definidos en el modelo
        if (!$this->validate($userRegister->registroRules, $userRegister->registroErrors)) {
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

        // Validar las credenciales de inicio de sesión usando las reglas y errores del modelo
        if (!$this->validate($userModel->loginRules, $userModel->loginErrors)) {
            $data['titulo'] = 'Login';
            $data['validation'] = $this->validator;
            return view('contenido/login', $data);
        }

        $request = \Config\Services::request();
        $mail = $request->getPost('correo');
        $pass = $request->getPost('password');
        $sessionData = $userModel->verificarCredenciales($mail, $pass);

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
}
