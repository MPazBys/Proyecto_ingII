<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use App\Models\libros_model;
use App\Models\venta_model; 
use App\Models\detalle_venta_model;
use App\Models\formapago_model; 
use App\Models\persona_model;
use App\Models\provincias_model;
use App\Models\localidades_model;
use App\Models\direccion_model;

class CarritoController extends BaseController {

    // Declaración de propiedades tipadas para inyección de modelos
    protected libros_model $librosModel;
    protected venta_model $ventaModel;
    protected detalle_venta_model $detalleModel;
    protected formapago_model $formaPagoModel;
    protected persona_model $personaModel;
    protected provincias_model $provinciaModel;
    protected localidades_model $localidadModel;
    protected direccion_model $direccionModel;

    /**
     * Inicializador del controlador. Carga todos los modelos mediante el Singleton nativo.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        
        $this->librosModel      = model(libros_model::class);
        $this->ventaModel       = model(venta_model::class);
        $this->detalleModel     = model(detalle_venta_model::class);
        $this->formaPagoModel   = model(formapago_model::class);
        $this->personaModel     = model(persona_model::class);
        $this->provinciaModel   = model(provincias_model::class);
        $this->localidadModel   = model(localidades_model::class);
        $this->direccionModel   = model(direccion_model::class);
    }

    /**
     * Muestra la vista del carrito con los productos agregados.
     */
    public function ver_carrito(): string|RedirectResponse {
        if (!session('login')) {
            return redirect()->to(base_url('login'))->with('error_login', 'Debes iniciar sesión para ver tu carrito.');
        }

        $cart = \Config\Services::cart();
        $data['items'] = $cart->contents();
        $data['formasPago'] = $this->formaPagoModel->findAll(); //En el diagrama de secuencia 10.3 esta como "obtenerFormaPago()"
        $data['provincias'] = $this->provinciaModel->orderBy('nombreProvincia', 'ASC')->findAll(); //En el diagrama de secuencia 10.3 esta como "obtenerProvincia()"
        $data['localidades'] = $this->localidadModel->orderBy('nombreLocalidad', 'ASC')->findAll(); //En el diagrama de secuencia 10.3 esta como "obtenerLocalidad()"
        $data['titulo'] = 'Carrito de compras';

        // Pre-carga estructurada de datos del cliente y su dirección
        $data['persona'] = null;
        $data['direccion'] = null;
        $data['idProvinciaCliente'] = null;
        $data['idLocalidadCliente'] = null;

        if (session('id')) {
            $data['persona'] = $this->personaModel->find(session('id'));
            if ($data['persona'] && !empty($data['persona']['idDireccion'])) {
                $direccion = $this->direccionModel->find($data['persona']['idDireccion']);
                if ($direccion) {
                    $data['direccion'] = $direccion;
                    $data['idLocalidadCliente'] = $direccion['idLocalidad'];
                    if ($loc = $this->localidadModel->find($direccion['idLocalidad'])) {
                        $data['idProvinciaCliente'] = $loc['idProvincia'];
                    }
                }
            }
        }

        return $this->cargar_vistas_cliente('contenido/carrito', $data);
    }

    /**
     * Agrega un producto al carrito verificando previamente el stock.
     */
    public function agregar_carrito(): RedirectResponse {
        if (!session('login')) {
            return redirect()->to(base_url('login'))->with('error_login', 'Debes iniciar sesión para agregar productos al carrito.');
        }

        $cart = \Config\Services::cart();
        $productoData = $this->librosModel->find($this->request->getPost('id'));

        if (!$productoData) {
            return redirect()->back()->with('msj', 'Producto no encontrado');
        }

        if ($productoData['stockLibro'] <= 0) {
            return redirect()->back()->with('msj', 'No hay stock disponible para este libro.');
        }

        $rowidExistente = null;
        foreach ($cart->contents() as $item) {
            if ($item['id'] == $productoData['idLibro']) {
                $rowidExistente = $item['rowid'];
                break;
            }
        }

        if ($rowidExistente) {
            $currentQty = $cart->getItem($rowidExistente)['qty'];
            if (($currentQty + 1) > $productoData['stockLibro']) {
                return redirect()->back()->with('msj', 'No puedes añadir más unidades de este libro, ya has alcanzado el stock disponible.');
            }
            $cart->update(['rowid' => $rowidExistente, 'qty' => $currentQty + 1]);
            session()->setFlashdata('mensaje', 'Cantidad del libro actualizada en el carrito.'); 
        } else {
            $cart->insert([
                'id'         => $productoData['idLibro'],
                'name'       => $productoData['nombreLibro'],
                'price'      => $productoData['precioLibro'],
                'qty'        => 1,
                'stockLibro' => $productoData['stockLibro']
            ]);
            session()->setFlashdata('mensaje', 'Libro agregado al carrito correctamente.');
        }

        return redirect()->route('ver_carrito'); 
    }

    /**
     * Unifica aumentar y disminuir cantidades en una sola función.
     */
    public function actualizar_cantidad(string $rowid, string $operacion): RedirectResponse {
        if (!session('login')) {
            return redirect()->to(base_url('login'))->with('error_login', 'Debes iniciar sesión para modificar tu carrito.');
        }

        $cart = \Config\Services::cart();
        $items = $cart->contents();

        if (!isset($items[$rowid])) {
            return redirect()->back()->with('msj', 'Item no encontrado en el carrito');
        }

        $item = $items[$rowid];
        $newQty = $item['qty'];

        if ($operacion === 'aumentar') {
            if ($item['qty'] >= $item['stockLibro']) {
                return redirect()->back()->with('msj', 'No hay más stock disponible');
            }
            $newQty = $item['qty'] + 1;
        } elseif ($operacion === 'disminuir') {
            if ($item['qty'] <= 1) {
                return redirect()->back()->with('msj', 'No puedes tener menos de 1 producto');
            }
            $newQty = $item['qty'] - 1;
        } else {
            return redirect()->back()->with('msj', 'Operación no válida');
        }

        $cart->update(['rowid' => $rowid, 'qty' => $newQty]);
        return redirect()->back()->with('mensaje', 'Cantidad actualizada');
    }

    /**
     * Elimina un item particular o vacía el carrito por completo.
     */
    public function eliminar_item(?string $rowid = null): RedirectResponse {
        if (!session('login')) {
            return redirect()->to(base_url('login'))->with('error_login', 'Debes iniciar sesión para modificar tu carrito.');
        }

        $cart = \Config\Services::cart();

        if ($rowid === null || $rowid === 'all') {
            $cart->destroy();
            $mensaje = 'El carrito se vació correctamente!';
        } else {
            $cart->remove($rowid);
            $mensaje = 'Libro eliminado correctamente!';
        }

        return redirect()->route('ver_carrito')->with('mensaje', $mensaje);
    }

    /**
     * Procesa la compra utilizando transacciones atómicas limpias.
     */
    public function procesar_finalizar_compra(): RedirectResponse {
        if (!session('login')) {
            return redirect()->to(base_url('login'))->with('error_login', 'Debes iniciar sesión para finalizar la compra.');
        }

        $cart = \Config\Services::cart();
        $cartItems = $cart->contents();

        if (empty($cartItems)) {
            return redirect()->route('ver_carrito')->with('msj', 'Tu carrito está vacío. Agrega productos antes de finalizar la compra.');
        }

        $formaEnvio = (string)$this->request->getPost('selectedFormaEnvio');
        $formaPago = (string)$this->request->getPost('selectedFormaPago');

        // 1. VALIDACIÓN AISLADA
        $validationResult = $this->validar_datos_compra($formaEnvio, $formaPago);
        if ($validationResult !== true) {
            return redirect()->back()->withInput()->with('errors', $validationResult);
        }

        $persona = $this->personaModel->find(session('id'));

        // Inicio del flujo transaccional
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 2. CONTROL DE STOCK PRIVADO (Lanza excepción si falla)
            $this->verificar_stock_carrito($cartItems);

            // 3. PROCESAMIENTO DE DIRECCIÓN (Si aplica)
            $idDireccion = !empty($persona['idDireccion']) ? (int)$persona['idDireccion'] : null;
            if ($formaEnvio === '2') {
                $idDireccion = $this->guardar_o_actualizar_direccion($this->request->getPost(), $idDireccion);
            }

            // 4. ACTUALIZACIÓN DEL CLIENTE
            $personaUpdateData = [
                'telefono' => $this->request->getPost('telefono'),
                'dni'      => intval($this->request->getPost('dni'))
            ];
            if ($formaEnvio === '2') {
                $personaUpdateData['idDireccion'] = $idDireccion;
            }
            $this->personaModel->update(session('id'), $personaUpdateData);

            // 5. REGISTRO CABECERA DE VENTA
            $venta_id = $this->ventaModel->insert([
                'idCliente'  => session('id'),
                'fecha'      => date('Y-m-d'),
                'idPago'     => intval($formaPago),
                'formaEnvio' => intval($formaEnvio),
                'total'      => $cart->total(),
                'estado'     => 'Pendiente'
            ]);

            if ($venta_id === false) {
                throw new \Exception("Error al insertar la venta.");
            }

            // 6. PROCESAMIENTO MASIVO DE DETALLES Y STOCK
            $this->procesar_items_carrito($cartItems, (int)$venta_id);

            // ==========================================
            //  ENVIAR MAIL CON DETALLES DE COMPRA
            // ==========================================
            $this->enviar_comprobante_compra((int)$venta_id, $persona, $cartItems, $formaEnvio, $formaPago);

            $db->transCommit();
            $cart->destroy();

            return redirect()->to(base_url("gracias_por_tu_compra"))->with('mensaje', '¡Compra realizada con éxito!');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en procesar_finalizar_compra: ' . $e->getMessage());
            return redirect()->route('ver_carrito')->with('msj', $e->getMessage());
        }
    }

    /**
     * Muestra la pantalla final de agradecimiento de compra.
     */
    public function gracias_por_tu_compra(): string {
        $data['titulo'] = '¡Compra Realizada!';
        return $this->cargar_vistas_cliente('contenido/gracias_compra', $data);
    }



    // ===================================
    // ENCAPSULAMIENTOS Y MÉTODOS PRIVADOS
    // ===================================

    /**
     * Centraliza la carga repetitiva de bloques de layout del frontend.
     */
    private function cargar_vistas_cliente(string $vistaContenido, array $data = []): string {
        return view('plantilla/header_view', $data) . view('plantilla/nav_view') . view($vistaContenido, $data) . view('plantilla/footer_view');
    }

    /**
     * Recorre preventivamente el carrito aislando el control lógico de stock.
     */
    private function verificar_stock_carrito(array $cartItems): void {
        foreach ($cartItems as $item) {
            $libro = $this->librosModel->find($item['id']);
            if (!$libro || $libro['stockLibro'] < $item['qty']) {
                $nombreLibro = $libro ? $libro['nombreLibro'] : $item['name'];
                throw new \Exception('Lo sentimos, el stock de "' . $nombreLibro . '" ya no está disponible. Por favor, revisa tu carrito.');
            }
        }
    }

    /**
     * Centraliza las reglas de validación de los datos de la compra.
     */
    private function validar_datos_compra(string $formaEnvio, string $formaPago): array|bool {
        $validation = \Config\Services::validation();

        $rules = [
            'selectedFormaEnvio' => ['rules' => 'required|in_list[1,2]', 'errors' => ['required' => 'Debe seleccionar una forma de envío.', 'in_list' => 'La forma de envío no es válida.']],
            'selectedFormaPago'  => ['rules' => 'required|in_list[1,2]', 'errors' => ['required' => 'Debe seleccionar una forma de pago.', 'in_list' => 'La forma de pago no es válida.']],
            'telefono'           => ['rules' => 'required|regex_match[/^\d{10,15}$/]', 'errors' => ['required' => 'El teléfono es obligatorio.', 'regex_match' => 'El teléfono debe tener entre 10 y 15 dígitos numéricos sin 0 ni 15.']],
            'dni'                => ['rules' => 'required|regex_match[/^\d{7,9}$/]', 'errors' => ['required' => 'El DNI es obligatorio.', 'regex_match' => 'El DNI debe tener entre 7 y 9 dígitos numéricos.']]
        ];

        if ($formaEnvio === '2') {
            $rules['calle']        = ['rules' => 'required|regex_match[/^(?=.*[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]).+$/]', 'errors' => ['required' => 'La calle es obligatoria.', 'regex_match' => 'La calle debe contener texto alfabético.']];
            $rules['altura']       = ['rules' => 'required|regex_match[/^\d{3,5}$/]', 'errors' => ['required' => 'La altura es obligatoria.', 'regex_match' => 'La altura debe tener entre 3 y 5 dígitos.']];
            $rules['pisoDepto']    = ['rules' => 'permit_empty|regex_match[/^(?=.*[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]).+$/]', 'errors' => ['regex_match' => 'El piso/depto debe contener al menos una letra.']];
            $rules['consideraciones'] = ['rules' => 'permit_empty|regex_match[/^(?=.*[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]).+$/]', 'errors' => ['regex_match' => 'Las consideraciones deben contener al menos una letra.']];
            $rules['provincia']    = ['rules' => 'required', 'errors' => ['required' => 'La provincia es obligatoria.']];
            $rules['idLocalidad']  = ['rules' => 'required', 'errors' => ['required' => 'La ciudad es obligatoria.']];
        }

        if ($formaPago === '2') {
            $rules['tarjeta']     = ['rules' => 'required|regex_match[/^\d{16}$/]', 'errors' => ['required' => 'El número de tarjeta es obligatorio.', 'regex_match' => 'La tarjeta debe tener exactamente 16 dígitos.']];
            $rules['vencimiento'] = ['rules' => 'required|regex_match[/^(0[1-9]|1[0-2])\/[0-9]{2}$/]', 'errors' => ['required' => 'La fecha de vencimiento es obligatoria.','regex_match' => 'El formato de vencimiento debe ser numérico MM/AA (Por ejemplo: 06/26).']];
            $rules['cvv']         = ['rules' => 'required|regex_match[/^\d{3,4}$/]', 'errors' => ['required' => 'El CVV es obligatorio.', 'regex_match' => 'El CVV debe tener 3 o 4 dígitos.']];
        }

        $validation->setRules($rules);
        return $validation->withRequest($this->request)->run() ? true : $validation->getErrors();
    }

    /**
     * Gestiona de forma inteligente el guardado o la actualización del domicilio del cliente.
     */
    private function guardar_o_actualizar_direccion(array $postData, ?int $idDireccionExistente): int {
        $direccionData = [
            'calle'           => $postData['calle'],
            'altura'          => intval($postData['altura']),
            'pisoDepto'       => $postData['pisoDepto'] ?: null,
            'consideraciones' => $postData['consideraciones'] ?: null,
            'idLocalidad'     => intval($postData['idLocalidad']),
        ];

        if ($idDireccionExistente !== null && $idDireccionExistente > 0) {
            $this->direccionModel->update($idDireccionExistente, $direccionData);
            return $idDireccionExistente;
        }

        $insertedId = $this->direccionModel->insert($direccionData);
        if ($insertedId === false) {
            throw new \Exception("Error al insertar la dirección de envío.");
        }
        return (int)$insertedId;
    }

    /**
     * Recorre masivamente los registros del carrito insertando su detalleventa y descontando stock.
     */
    private function procesar_items_carrito(array $cartItems, int $ventaId): void {
        foreach ($cartItems as $item) {
            $libro = $this->librosModel->find($item['id']);
            if (!$libro) {
                throw new \Exception("Libro no encontrado en base de datos durante el procesado.");
            }

            $this->detalleModel->insert([
                'idVenta'        => $ventaId,
                'idLibro'        => $item['id'],
                'cantidad'       => $item['qty'],
                'precioUnitario' => $item['price']
            ]);

            $this->librosModel->update($item['id'], ['stockLibro' => $libro['stockLibro'] - $item['qty']]);
        }
    }

    /**
     * Construye un HTML con el comprobante de la compra y lo envía al cliente por Gmail.
     */
    private function enviar_comprobante_compra(int $ventaId, array $persona, array $cartItems, string $formaEnvio, string $formaPago): void {
        $emailService = \Config\Services::email();

        // Traducir ID de envío a texto legible
        $envioTexto = ($formaEnvio === '2') ? 'Envío a domicilio' : 'Retiro en sucursal';
        
        // Traducir ID de pago a texto legible
        $pagoTexto = ($formaPago === '2') ? 'Tarjeta de Crédito/Débito' : 'Transferencia Bancaria';

        $emailService->setTo($persona['correoPersona']);
        $emailService->setSubject('Detalle de tu compra #' . $ventaId . ' - Librería M&P');

        // --- DISEÑO DEL CUERPO DEL CORREO (HTML) ---
        $html = '<h2>¡Gracias por tu compra en Librería M&P!</h2>';
        $html .= '<p>Hola <strong>' . esc($persona['nombrePersona']) . ' ' . esc($persona['apellidoPersona']) . '</strong>,</p>';
        $html .= '<p>Tu pedido ha sido procesado con éxito. A continuación te detallamos los datos de tu compra:</p>';
        
        $html .= '<hr>';
        $html .= '<h3>Datos del Comprador</h3>';
        $html .= '<ul>';
        $html .= '<li><strong>DNI:</strong> ' . esc($this->request->getPost('dni')) . '</li>';
        $html .= '<li><strong>Teléfono:</strong> ' . esc($this->request->getPost('telefono')) . '</li>';
        $html .= '<li><strong>Forma de Envío:</strong> ' . $envioTexto . '</li>';
        
        // Si es envío a domicilio, sumamos los datos de dirección al mail
        if ($formaEnvio === '2') {
            $html .= '<li><strong>Dirección:</strong> ' . esc($this->request->getPost('calle')) . ' ' . esc($this->request->getPost('altura'));
            if ($this->request->getPost('pisoDepto')) {
                $html .= ' (Piso/Depto: ' . esc($this->request->getPost('pisoDepto')) . ')';
            }
            $html .= '</li>';
        }
        
        $html .= '<li><strong>Forma de Pago:</strong> ' . $pagoTexto . '</li>';
        $html .= '</ul>';

        $html .= '<hr>';
        $html .= '<h3>Detalle de los Productos</h3>';
        
        // Tabla con diseño limpio usando estilos inline para que Gmail lo tome bien
        $html .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; border-color: #ddd;">';
        $html .= '<thead style="background-color: #f2f2f2;">';
        $html .= '<tr>';
        $html .= '<th align="left">Libro</th>';
        $html .= '<th align="center">Cantidad</th>';
        $html .= '<th align="right">Precio Unitario</th>';
        $html .= '<th align="right">Subtotal</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $totalCompra = 0;
        foreach ($cartItems as $item) {
            $subtotal = $item['qty'] * $item['price'];
            $totalCompra += $subtotal;

            $html .= '<tr>';
            $html .= '<td>' . esc($item['name']) . '</td>';
            $html .= '<td align="center">' . $item['qty'] . '</td>';
            $html .= '<td align="right">$' . number_format($item['price'], 2, ',', '.') . '</td>';
            $html .= '<td align="right">$' . number_format($subtotal, 2, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr>';
        $html .= '<td colspan="3" align="right"><strong>Total Facturado:</strong></td>';
        $html .= '<td align="right" style="background-color: #f9f9f9;"><strong>$' . number_format($totalCompra, 2, ',', '.') . '</strong></td>';
        $html .= '</tr>';
        $html .= '</tbody>';
        $html .= '</table>';

        $html .= '<br><p>Puedes revisar el estado de tu pedido en cualquier momento ingresando a tu perfil en nuestra web.</p>';
        $html .= '<p>Atentamente,<br><strong>El equipo de M&P.</strong></p>';

        $emailService->setMessage($html);

        // Si el motor SMTP de Google llega a fallar, disparamos la excepción para activar el rollback
        if (!$emailService->send()) {
            throw new \Exception("No se pudo enviar el correo de comprobante, compra cancelada por seguridad.");
        }
    }
}