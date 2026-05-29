<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use App\Models\venta_model;
use App\Models\detalle_venta_model;
use App\Models\persona_model;
use App\Models\formapago_model;
use App\Models\libros_model;

class VentaController extends BaseController {

    protected venta_model $ventaModel;
    protected detalle_venta_model $detalleModel;
    protected persona_model $personaModel;
    protected formapago_model $formaPagoModel;
    protected libros_model $librosModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        
        $this->ventaModel     = model(venta_model::class);
        $this->detalleModel   = model(detalle_venta_model::class);
        $this->personaModel   = model(persona_model::class);
        $this->formaPagoModel = model(formapago_model::class);
        $this->librosModel    = model(libros_model::class);
    }

    /**
     * Muestra el panel de administración ordenando cronológicamente los pedidos por su estado.
     */
    public function gestionar_ventas(): string|RedirectResponse {
        if (!session('login') || session('perfil') != 1) {
            return redirect()->to(base_url('login'))->with('error_login', 'Acceso no autorizado.');
        }

        // COMPRESIÓN: Llamadas al método privado auxiliar con su ordenamiento correspondiente
        $data['ventasPendientes']  = $this->obtener_ventas_por_estado('Pendiente', 'ASC');
        $data['ventasEnviadas']    = $this->obtener_ventas_por_estado('Enviado', 'ASC');
        $data['ventasFinalizadas'] = $this->obtener_ventas_por_estado('Finalizado', 'DESC');

        // Mapeo masivo de detalles agrupados por idVenta
        $detalles = $this->detalleModel->join('libros', 'libros.idLibro = detalleventa.idLibro')->findAll();
        $detallesPorVenta = [];
        foreach ($detalles as $detalle) {
            $detallesPorVenta[$detalle['idVenta']][] = $detalle;
        }

        $data['detallesPorVenta'] = $detallesPorVenta;
        $data['titulo']           = 'Listar ventas';

        return view('plantilla/nav_admin_view', $data) .
               view('backend/ventas', $data) .
               view('plantilla/footer_admin_view');
    }

    /**
     * Genera un listado HTML dinámico con los detalles de una venta.
     */
    public function detalle_venta(int $idVenta): string {
        $detalles = $this->detalleModel->where('idVenta', $idVenta)->findAll();

        if (empty($detalles)) {
            return 'No hay detalles para esta venta.';
        }

        $html = '<ul class="list-group">';
        foreach ($detalles as $detalle) {
            $libro = $this->librosModel->find($detalle['idLibro']);
            $nombreLibro = $libro ? $libro['nombreLibro'] : 'Libro desconocido';
            $html .= '<li class="list-group-item">Libro: <strong>' . esc($nombreLibro) . '</strong> - Cantidad: ' . $detalle['cantidad'] . ' - $' . $detalle['precioUnitario'] . '</li>';
        }
        return $html . '</ul>';
    }

    /**
     * Cambia el estado de una venta aplicando las reglas de negocio transaccionales y de correo.
     */
    public function cambiar_estado(int $idVenta, string $nuevoEstado): RedirectResponse {
        if (!session('login') || session('perfil') != 1) {
            return redirect()->to(base_url('login'))->with('error_login', 'Acceso no autorizado.');
        }

        $venta = $this->ventaModel->find($idVenta);
        if (!$venta) {
            return redirect()->route('gestionar_ventas')->with('msj', 'La venta no existe.');
        }

        $cliente = $this->personaModel->find($venta['idCliente']);
        if (!$cliente) {
            return redirect()->route('gestionar_ventas')->with('msj', 'Cliente no encontrado.');
        }

        // Normalización estricta de cadenas de texto
        $nuevoEstado  = ucfirst(strtolower($nuevoEstado));
        $estadoActual = $venta['estado'];
        $formaEnvio   = (int)$venta['formaEnvio']; // 1 = Retiro, 2 = Domicilio

        // MODULARIZACIÓN: Validación aislada del ciclo de vida del pedido
        if (!$this->es_transicion_valida($estadoActual, $nuevoEstado, $formaEnvio)) {
            return redirect()->route('gestionar_ventas')->with('msj', 'Transición de estado denegada por reglas de negocio del despacho.');
        }

        // Persistencia atómica
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $this->ventaModel->update($idVenta, ['estado' => $nuevoEstado]);

            // Notificación vía SMTP/mail centralizado
            $this->enviar_notificacion_estado($idVenta, $cliente, $nuevoEstado, $formaEnvio);

            $db->transCommit();
            return redirect()->route('gestionar_ventas')->with('mensaje', 'El pedido #' . $idVenta . ' cambió a ' . $nuevoEstado . ' con éxito.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->route('gestionar_ventas')->with('msj', 'Error crítico al alterar estado: ' . $e->getMessage());
        }
    }

    // ===================================
    // ENCAPSULAMIENTOS Y MÉTODOS PRIVADOS
    // ===================================

    /**
     * Abstrae y unifica la construcción del Query Builder para listar pedidos.
     */
    private function obtener_ventas_por_estado(string $estado, string $orden): array {
        return $this->ventaModel
            ->join('persona', 'persona.idPersona = venta.idCliente')
            ->join('formapago', 'formapago.idPago = venta.idPago')
            ->where('venta.estado', $estado)
            ->orderBy('venta.idVenta', $orden)
            ->findAll();
    }

    /**
     * Aplica de forma estricta las reglas de la máquina de estados de las entregas.
     */
    private function es_transicion_valida(string $actual, string $nuevo, int $formaEnvio): bool {
        if ($formaEnvio === 1) {
            // Retiro presencial: Solo se permite ir directo de Pendiente a Finalizado
            return ($actual === 'Pendiente' && $nuevo === 'Finalizado');
        } 
        
        if ($formaEnvio === 2) {
            // Envío domiciliario: Sigue el pipeline secuencial obligatorio
            if ($actual === 'Pendiente') {
                return ($nuevo === 'Enviado');
            }
            if ($actual === 'Enviado') {
                return ($nuevo === 'Finalizado');
            }
        }

        return false;
    }

    /**
     * Construye y despacha los correos electrónicos informativos al cliente.
     */
    private function enviar_notificacion_estado(int $idVenta, array $cliente, string $estado, int $formaEnvio): void {
        $email = \Config\Services::email();
        $email->setTo($cliente['correoPersona']);

        if ($estado === 'Enviado') {
            $email->setSubject('¡Tu pedido está en camino! - Librería M&P');
            $html = '<h2>¡Hola ' . esc($cliente['nombrePersona']) . '!</h2>';
            $html .= '<p>Queremos informarte que tu pedido <strong>#' . $idVenta . '</strong> ha sido despachado y está en camino a tu domicilio.</p>';
            $html .= '<p>Nuestros repartidores se contactarán contigo al número de teléfono registrado al momento de la entrega.</p>';
            $html .= '<br><p>¡Gracias por elegir Librería M&P!</p>';
        } elseif ($estado === 'Finalizado') {
            $email->setSubject(($formaEnvio === 1) ? '¡Retiraste tu pedido con éxito! - Librería M&P' : 'Tu pedido fue entregado con éxito - Librería M&P');
            
            $html = '<h2>¡Hola ' . esc($cliente['nombrePersona']) . '!</h2>';
            $html .= '<p>Tu pedido <strong>#' . $idVenta . '</strong> ha sido ' . (($formaEnvio === 1) ? 'retirado de nuestra sucursal' : 'entregado correctamente') . '.</p>';
            $html .= '<p>Esperamos que disfrutes de tu compra. Si tienes alguna consulta, no dudes en escribirnos.</p>';
            $html .= '<br><p>¡Muchas gracias por tu confianza!<br><strong>El equipo de M&P.</strong></p>';
        } else {
            return;
        }

        $email->setMessage($html);

        if (!$email->send()) {
            log_message('error', 'Error al despachar notificación SMTP para venta #' . $idVenta);
        }
    }
}