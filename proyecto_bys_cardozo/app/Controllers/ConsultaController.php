<?php

namespace App\Controllers;

use App\Models\consulta_model;

class ConsultaController extends BaseController
{
    /**
     * Registra una nueva consulta de contacto enviada por el usuario
     */
    public function add_consulta()
    {
        if (!session('login')) {
            return redirect()->route('login')->with('error_login', 'Debes iniciar sesión para realizar una consulta.');
        }

        $consultaModel = new consulta_model();

        // Validar el formulario de consulta usando las reglas y errores definidos en el modelo
        if (!$this->validate($consultaModel->addConsultaRules, $consultaModel->addConsultaErrors)) {
            $data['titulo'] = 'Contactos';
            $data['validation'] = $this->validator->getErrors();
            return view('contenido/contactos', $data);
        }

        $request = \Config\Services::request();
        $data = [
            'asunto'    => $request->getPost('motivo'),
            'mensaje'   => $request->getPost('consulta'),
            'idPersona' => session('id'),
            'respondido'=> 0
        ];

        $consultaModel->insert($data);

        return redirect()->route('contactos')->with('mensajeConsulta', 'Su consulta se envió correctamente!');
    }

    public function admin()
    {
        $model = new consulta_model();
        $data['consultas'] = $model->getConsultasConPersona();
        $data['titulo'] = 'Consultas';

        return view('backend/consultas', $data);
    }

    public function responder($idConsulta)
    {
        $model = new consulta_model();
        $consulta = $model->getConsultaConPersona($idConsulta);

        if ($consulta) {
            // Marcar consulta como respondida en la base de datos
            $model->update($idConsulta, ['respondido' => 1]);

            // Construir el enlace para redactar correo en Gmail
            $to = urlencode($consulta['correo']);
            $subject = urlencode('Respuesta a su consulta: ' . $consulta['asunto']);
            $body = urlencode("\n\n---\nConsulta original de " . $consulta['nombreApellido'] . ":\n\"" . $consulta['mensaje'] . "\"");
            $gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1&to={$to}&su={$subject}&body={$body}";

            // Redirigir a Gmail para redactar la respuesta
            return redirect()->to($gmailUrl);
        }

        session()->setFlashdata('mensaje', 'La consulta no existe.');
        return redirect()->route('consultas');
    }

    public function eliminar($idConsulta)
    {
        $model = new consulta_model();
        if ($model->delete($idConsulta)) {
            session()->setFlashdata('mensaje', 'Consulta eliminada correctamente.');
        } else {
            session()->setFlashdata('mensaje', 'Error al eliminar la consulta.');
        }

        return redirect()->route('consultas');
    }
}
