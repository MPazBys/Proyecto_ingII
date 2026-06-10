<?php

namespace App\Controllers;

use App\Models\consulta_model;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controlador principal para la gestión de consultas de contacto.
 * Administra el envío por parte de los clientes y el procesamiento de respuestas internas del backend.
 */
class ConsultaController extends BaseController
{
    // Propiedad tipada para la inyección del modelo principal
    protected consulta_model $consultaModel;

    // --- REGLAS DE VALIDACIÓN: CONSULTAS (CLIENTES) ---
    protected array $addConsultaRules = [
        'motivo'   => 'required|max_length[100]',
        'consulta' => 'required|max_length[250]|min_length[10]',
    ];

    protected array $addConsultaErrors = [
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

    // --- REGLAS DE VALIDACIÓN: RESPUESTAS (ADMINISTRADORES) ---
    protected array $replyConsultaRules = [
        'respuesta' => 'required|min_length[5]|regex_match[/[a-zA-ZñÑáéíóúüÁÉÍÓÚÜ]/]',
    ];

    protected array $replyConsultaErrors = [
        'respuesta' => [
            'required'    => 'El campo de respuesta no puede estar vacío.',
            'min_length'  => 'La respuesta debe ser más descriptiva (mínimo 5 caracteres).',
            'regex_match' => 'La respuesta debe contener letras y no solo números o símbolos.',
        ],
    ];

    /**
     * Constructor del controlador.
     * Carga el modelo una sola vez utilizando el patrón Singleton nativo de CI4.
     */
    public function __construct()
    {
        $this->consultaModel = model(consulta_model::class);
    }

    /**
     * Registra una nueva consulta de contacto enviada por el usuario (Frontend).
     */
    public function add_consulta(): RedirectResponse|string
    {
        if (!session('login')) {
            return redirect()->route('login')->with('error_login', 'Debes iniciar sesión para realizar una consulta.');
        }

        // Validación nativa de la consulta de entrada
        if (!$this->validate($this->addConsultaRules, $this->addConsultaErrors)) {
            $data['titulo'] = 'Contactos';
            $data['validation'] = $this->validator->getErrors();
            return view('contenido/contactos', $data);
        }

        $request = \Config\Services::request();
        $data = [
            'asunto'     => $request->getPost('motivo'),
            'mensaje'    => $request->getPost('consulta'),
            'idPersona'  => session('id'),
            'respondido' => 0
        ];

        $this->consultaModel->insert($data);

        return redirect()->route('contactos')->with('mensajeConsulta', 'Su consulta se envió correctamente!');
    }

    /**
     * Muestra la tabla general de control de consultas en el panel de administrador.
     */
    public function admin(): string
    {
        $data['consultas'] = $this->getConsultasConPersona();
        $data['titulo'] = 'Consultas';

        return view('backend/consultas', $data);
    }

    /**
     * Carga el formulario interno para responder a una consulta específica.
     */
    public function responder(int $idConsulta): string|RedirectResponse
    {
        $consulta = $this->getConsultaConPersona($idConsulta);

        if (!$consulta) {
            session()->setFlashdata('mensaje', 'La consulta no existe.');
            return redirect()->route('consultas');
        }

        $data = [
            'consulta' => $consulta,
            'titulo'   => 'Responder Consulta'
        ];

        return view('backend/responder_consulta_view', $data);
    }

    /**
     * Procesa la contestación redactada por el administrador, actualiza la base de datos
     * de forma transaccional y despacha la notificación por correo utilizando XAMPP mailtodo.
     */
    public function procesar_respuesta(): RedirectResponse
    {
        $idConsulta = (int)$this->request->getPost('idConsulta');

        // 1. EARLY RETURN: Valida que la respuesta sea correcta usando el Service nativo
        if (!$this->validate($this->replyConsultaRules, $this->replyConsultaErrors)) {
            return redirect()->back()
                             ->withInput()
                             ->with('validation', $this->validator->getErrors());
        }

        // 2. EARLY RETURN: Valida la existencia física de la consulta antes de operar
        $consulta = $this->getConsultaConPersona($idConsulta);
        if (!$consulta) {
            return redirect()->route('consultas')->with('error', 'La consulta no existe.');
        }

        // 3. EARLY RETURN: Control riguroso de sesión administrativa activa
        $adminId = session('id');
        if (!$adminId) {
            return redirect()->route('login')->with('error_login', 'Debes iniciar sesión para responder.');
        }

        $respuestaText = $this->request->getPost('respuesta');

        // 4. FLUJO ATÓMICO TRANSACCIONAL (ACID)
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Actualización del registro en base de datos asociando la consulta con la respuesta y el admin firmante
            $this->consultaModel->update($idConsulta, [
                'respuestaText'   => $respuestaText,
                'idAdminResponde' => $adminId,
                'respondido'      => 1
            ]);

            $db->getConnection()->next_result(); //limpieza de resultados para evitar bloqueos en SP posteriores

            // Construcción del envío del email utilizando el driver nativo ('mail')
            $email = \Config\Services::email();
            $email->setTo($consulta['correo']);
            $email->setSubject('Respuesta a su consulta: ' . $consulta['asunto']);

            $mensajeEmail = "Hola " . $consulta['nombreApellido'] . ",\n\n";
            $mensajeEmail .= "Hemos respondido a su consulta sobre: \"" . $consulta['asunto'] . "\"\n\n";
            $mensajeEmail .= "Respuesta:\n" . $respuestaText . "\n\n";
            $mensajeEmail .= "Atentamente,\nEl equipo de M&P.";

            $email->setMessage($mensajeEmail);

            // Si el motor 'mailtodo' de XAMPP no logra interceptar el archivo, forzamos rollback de seguridad
            if (!$email->send()) {
                throw new \RuntimeException('No se pudo enviar el correo electrónico a través del servidor de Gmail.');
            }

            $db->transCommit();
            return redirect()->route('consultas')->with('mensaje', 'Respuesta enviada y notificada con éxito.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Error al procesar la respuesta: ' . $e->getMessage());
        }
    }

    // --- MÉTODOS DE LLAMADAS A PROCEDIMIENTOS ALMACENADOS CON RELACIONES (JOINs) ---

    /**
     * Recupera el listado completo de consultas ordenando de forma prioritaria las pendientes (FIFO).
     */
    private function getConsultasConPersona(): array
    {
        // Ejecutamos el SP
        return \Config\Database::connect()->query("CALL sp_obtener_consultas_completas()")->getResultArray();
    }

    /**
     * Recupera los metadatos de una única consulta específica mapeando sus relaciones.
     */
    private function getConsultaConPersona(int $idConsulta): ?array
    {
        $db = \Config\Database::connect();
        // Ejecutamos el SP 
        $query = $db->query("CALL sp_obtener_consulta_por_id(?)", [$idConsulta]);
        
        $db->getConnection()->next_result();

        if($query){
            return $query->getRowArray();
        }

        return null;
    }
}