<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use App\Libraries\Estado\EstadoVenta; // importación de la interfaz del patrón Estado
use App\Models\venta_model;
use App\Models\detalle_venta_model;
use App\Models\persona_model;
use App\Models\formapago_model;
use App\Models\libros_model;
use App\Models\direccion_model;
use App\Models\localidades_model;
use App\Models\provincias_model;

//El método que posee la impletación del patron es cambiar_estado(int $idVenta, string $nuevoEstado)

class VentaController extends BaseController {

    protected venta_model $ventaModel;
    protected detalle_venta_model $detalleModel;
    protected persona_model $personaModel;
    protected formapago_model $formaPagoModel;
    protected libros_model $librosModel;
    protected direccion_model $direccionModel;
    protected localidades_model $localidadModel;
    protected provincias_model $provinciaModel;


    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        
        $this->ventaModel     = model(venta_model::class);
        $this->detalleModel   = model(detalle_venta_model::class);
        $this->personaModel   = model(persona_model::class);
        $this->formaPagoModel = model(formapago_model::class);
        $this->librosModel    = model(libros_model::class);
        $this->direccionModel  = model(direccion_model::class);
        $this->localidadModel   = model(localidades_model::class);
        $this->provinciaModel   = model(provincias_model::class);
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

    /*
     * Cambia el estado de una venta delegando el control de la máquina de estados
     * y las acciones secundarias a los objetos del patrón Estado.
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

        $nuevoEstadoKey = strtolower($nuevoEstado);
        $estadoActualKey = strtolower($venta['estado']);
        $formaEnvio     = (int)$venta['formaEnvio'];

        try {
            // Validar e Instanciar el objeto del estado actual desde la Clase Abstracta
            if (!isset(EstadoVenta::MAPA[$estadoActualKey])) {
                throw new \InvalidArgumentException("Estado actual registrado en la BD no es válido: " . $venta['estado']);
            }

            $claseEstadoActual = EstadoVenta::MAPA[$estadoActualKey];
            /** @var EstadoVenta $estadoActualObj */
            $estadoActualObj = new $claseEstadoActual();

            // Delegar la validación lógica de la transición al objeto de estado
            $nuevoEstadoFormateado = ucfirst($nuevoEstadoKey); // Se asegura el formato CamelCase para guardar en BD
            if (!$estadoActualObj->cambiarEstado($venta, $nuevoEstadoFormateado, $formaEnvio)) {
                return redirect()->route('gestionar_ventas')->with('msj', 'Transición de estado denegada por reglas de negocio del despacho.');
            }

            // Persistencia atómica
            $db = \Config\Database::connect();
            $db->transBegin();

            // Actualizar el registro string en la base de datos
            $this->ventaModel->update($idVenta, ['estado' => $nuevoEstadoFormateado]);

            // Validar e Instanciar el nuevo estado para disparar sus efectos secundarios
            if (!isset(EstadoVenta::MAPA[$nuevoEstadoKey])) {
                throw new \InvalidArgumentException("El nuevo estado solicitado no es válido: " . $nuevoEstado);
            }
            $claseEstadoNuevo = EstadoVenta::MAPA[$nuevoEstadoKey];
            /** @var EstadoVenta $estadoNuevoObj */
            $estadoNuevoObj = new $claseEstadoNuevo();
            
            $estadoNuevoObj->ejecutarAccionPostTransicion($venta, $cliente);

            $db->transCommit();
            return redirect()->route('gestionar_ventas')->with('mensaje', 'El pedido #' . $idVenta . ' cambió a ' . $nuevoEstadoFormateado . ' con éxito.');
            
        } catch (\Exception $e) {
            // Aseguramos que $db exista antes de intentar el rollback en caso de fallar antes de la transacción
            if (isset($db)) {
                $db->transRollback();
            }
            return redirect()->route('gestionar_ventas')->with('msj', 'Error crítico al alterar estado: ' . $e->getMessage());
        }
    }

    // ===================================
    // ENCAPSULAMIENTOS Y MÉTODOS PRIVADOS
    // ===================================

    /**
     * Llama al procedimiento almacenado para obtener las ventas.
     */
    private function obtener_ventas_por_estado(string $estado, string $orden): array {
        $db = \Config\Database::connect();

        // Llamada al procedimiento almacenado con parámetros para filtrar por estado y ordenar por fecha
        $query = $db->query("CALL sp_obtener_ventas_por_estado(?, ?)", [$estado, $orden]);

        $db->getConnection()->next_result(); // Limpia el resultado del procedimiento almacenado para evitar conflictos con futuras consultas
        
        return $query->getResultArray();
    }

    
}