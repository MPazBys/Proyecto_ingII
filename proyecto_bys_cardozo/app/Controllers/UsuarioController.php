<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\consulta_model;
use App\Models\persona_model;

class UsuarioController extends BaseController {

    public function add_consulta() {
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();
        
        $validation->setRules([
            'nombreYapellido' => 'required|max_length[150]',
            'correo' => 'required|valid_email',
            'motivo' => 'required|max_length[100]',
            'consulta' => 'required|max_length[250]|min_length[10]',
        ], [ //Errors 
             'nombreYapellido' => [
                        'required'  => 'El nombre y apellido es requerido',
                        'max_length' => 'El nombre no puede superar los 50 caracteres'], 
             'correo' => [
                        'required'  => 'El correo es obligatorio', 
                        'valid_email'  =>  'Ladirección del correo debe ser válida'],
              'motivo' => [
                        'required'  => 'El motivo es obligatorio', 
                        "max_length"  => 'El motivo de la consulta debe tener como máximo 100 caracteres'], 
              'consulta' => [
                        'required'  => 'La consulta es requerida', 
                        "min_length"  => 'El motivo de la consulta debe tener como mínimo 10 caracteres',
                        "max_length"  => 'El motivo de la consulta debe tener como máximo 200 caracteres'], 
        ]);

        if ($validation -> withRequest($request) -> run ()) {
            $data = [
                'nombreApellido' => $request->getPost('nombreYapellido'),
                'correo' => $request->getPost('correo'),
                'asunto' => $request->getPost('motivo'),
                'mensaje' => $request->getPost('consulta'),
            ];

            $consulta = new consulta_model();
            $consulta -> insert($data); 
            return redirect( ) -> route('contactos') -> with ('mensajeConsulta', 'Su consulta se envio correctamente!');

        }else{
            $data ['titulo'] = 'Contactos'; 
            $data ['validation'] = $validation -> getErrors (); 
                return view('plantilla/header_view', $data).
                    view('plantilla/nav_view').
                    view('contenido/contactos').
                    view('plantilla/footer_view');
        }
    }

    public function add_cliente() {
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();

        $validation->setRules([
            'nombre' => 'required|alpha_space|min_length[2]|max_length[50]',
            'apellido' => 'required|alpha_space|min_length[2]|max_length[50]',
            'correo' => 'required|valid_email|is_unique[persona.correoPersona]|max_length[100]',
            'password' => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).+$/]',
            'confirm_password' => 'required|matches[password]',
        ], [
            'nombre' => [
                'required' => 'El nombre es obligatorio',
                'alpha_space' => 'Solo se permiten letras y espacios',
                'min_length' => 'Debe tener al menos 2 caracteres',
                'max_length' => 'No puede superar los 50 caracteres',
            ],
            'apellido' => [
                'required' => 'El apellido es obligatorio',
                'alpha_space' => 'Solo se permiten letras y espacios',
                'min_length' => 'Debe tener al menos 2 caracteres',
                'max_length' => 'No puede superar los 50 caracteres',
            ],
            'correo' => [
                'required' => 'El correo es obligatorio',
                'valid_email' => 'Debe ser un correo válido',
                'is_unique' => 'El cliente ya se encuentra registrado',
                'max_length' => 'El correo no puede superar los 100 caracteres',
            ],
            'password' => [
                'required' => 'Ingresar la contraseña es obligatorio',
                'min_length' => 'La contraseña debe tener como mínimo 8 caracteres',
                'regex_match' => 'Debe incluir mayúsculas, minúsculas, números y un carácter especial',
            ],
            'confirm_password' => [
                'required' => 'Repetir la contraseña es obligatorio',
                'matches' => 'Las contraseñas no coinciden',
            ]
        ]);


        if($validation->withRequest($request)->run()) {
            $data = [
                'nombrePersona' => $request->getPost('nombre'),
                'apellidoPersona' => $request->getPost('apellido'),
                'correoPersona' => $request->getPost('correo'),
                'contrasenia' => password_hash($request->getPost('password'), PASSWORD_BCRYPT),
                'idPerfil' => 2,
                'estadoUsuario' => 1
            ];

            $userRegister = new persona_model();
            $userRegister->insert($data);
            return redirect()->route('login')->with('mensaje', 'Su registro se realizo exitosamente!');
        } else {
            $data['titulo'] = 'Registrarse';
            $data['validation'] = $validation->getErrors();
            return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/registro').
            view('plantilla/footer_view');
        }
    }

    public function buscar_usuario(){
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $session = session();

        $validation->setRules([
            'correo' => 'required|valid_email|max_length[100]',
            'password' => 'required|min_length[8]',
        ], [
            'correo' => [
                'required' => 'El correo es obligatorio',
                'valid_email' => 'Debe ser un correo válido',
                'max_length' => 'El correo no puede superar los 100 caracteres'
            ],
            'password' => [
                'required' => 'Ingresar la contraseña es obligatorio',
                'min_length' => 'La contraseña debe tener como mínimo 8 caracteres',
            ],
        ]);

        if(! $validation->withRequest($request)->run()) {
            $data['titulo'] = 'Login';
            $data['validation'] = $validation;

            return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/login').
            view('plantilla/footer_view');
        }

        $mail = $request->getPost('correo');
        $pass = $request->getPost('password');

        $user_Model = new persona_model();

        $user = $user_Model->where('correoPersona', $mail)->where('estadoUsuario', 1)->first();

        if($user && password_verify($pass, $user['contrasenia'])){
            $data = [
                'id' => $user['idPersona'],
                'nombre' => $user['nombrePersona'],
                'apellido' => $user['apellidoPersona'],
                'perfil' => $user['idPerfil'],
                'login' => TRUE
            ];

            $session->set($data);
            switch($user['idPerfil']){
                case '1':
                    return redirect()->route('user_admin');
                    break;
                case '2':
                    return redirect()->route('/');
                    break;
            }
        } else {
            $data['titulo'] = 'Login';
            $data['validation'] = $validation;
            $data['error_login'] = 'Usuario y/o contraseña incorrectos';

            return view('plantilla/header_view', $data).
                view('plantilla/nav_view').
                view('contenido/login').
                view('plantilla/footer_view');
        }
    }

    public function cerrar_sesion(){
        $session = session();
        $session->destroy();
        return redirect()->route('login');
    }


    public function admin(){ 
        $model = new consulta_model();
        $data['consultas'] = $model->findAll();
        $data['titulo'] = 'Consultas';
        return view('plantilla/nav_admin_view', $data).
        view('backend/consultas').
        view('plantilla/footer_admin_view');
    }
    
    public function responder($idConsulta){
        $model = new consulta_model();
        $model->update($idConsulta, ['respondido' => 1]);

        session()->setFlashdata('mensaje', 'Consulta marcada como respondida.');
        return redirect()->route('consultas');
    }

    public function eliminar($idConsulta){
        $model = new consulta_model();
        if ($model->delete($idConsulta)) {
        session()->setFlashdata('mensaje', 'Consulta eliminada correctamente.');
    } else {
        session()->setFlashdata('mensaje', 'Error al eliminar la consulta.');
    }
        return redirect()->route('consultas');
    }
}