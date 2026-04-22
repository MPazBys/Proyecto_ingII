<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\libros_model;
use App\Models\categorias_model;
use App\Models\venta_model; 
use App\Models\detalle_venta_model;
use App\Models\formapago_model; 
use App\Models\persona_model;

class CarritoController extends BaseController {

    public function ver_carrito(){
        $cart = \Config\Services::cart();
        $formaPagoModel = new formapago_model();
        $personaModel = new persona_model();

        $formasPago = $formaPagoModel->findAll();
        $data['items'] = $cart->contents();

        // Obtener datos de la persona para precargar el modal
        $data['persona'] = null;
        if (session('id')) {
            $data['persona'] = $personaModel->where('idPersona', session('id'))->first();
        }

        $data['formasPago'] = $formasPago;
        $data['titulo'] = 'Carrito de compras';

        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/carrito', $data).
            view('plantilla/footer_view');
    }

    public function agregar_carrito(){
        $cart = \Config\Services::cart();
        $request = \Config\Services::request();
        $productoModel = new libros_model(); 

        $productoData = $productoModel->find($request->getPost('id'));

        if (!$productoData) {
            return redirect()->back()->with('msj', 'Producto no encontrado');
        }

        if ($productoData['stockLibro'] <= 0) {
            return redirect()->back()->with('msj', 'No hay stock disponible para este libro.');
        }

        // Identifica si el producto ya está en el carrito
        $rowidExistente = null;
        foreach ($cart->contents() as $item) {
            if ($item['id'] == $productoData['idLibro']) {
                $rowidExistente = $item['rowid'];
                break;
            }
        }

        $qtyToAdd = 1;

        if ($rowidExistente) {
            // Si el producto ya está en el carrito, obtenemos su cantidad actual
            $currentQty = $cart->getItem($rowidExistente)['qty'];
            if (($currentQty + $qtyToAdd) > $productoData['stockLibro']) {
                return redirect()->back()->with('msj', 'No puedes añadir más unidades de este libro, ya has alcanzado el stock disponible.');
            }
            // Actualizamos la cantidad del item existente
            $cart->update([
                'rowid' => $rowidExistente,
                'qty'   => $currentQty + $qtyToAdd
            ]);
            session()->setFlashdata('mensaje', 'Cantidad del libro actualizada en el carrito.'); 
        } else {
            // Si el producto no está en el carrito, lo agregamos
            $data = array(
                'id'    => $productoData['idLibro'],
                'name'  => $productoData['nombreLibro'],
                'price' => $productoData['precioLibro'],
                'qty'   => $qtyToAdd,
                'stockLibro' => $productoData['stockLibro'] //Validaciones futuras
            );
            $cart->insert($data);
            session()->setFlashdata('mensaje', 'Libro agregado al carrito correctamente.');
        }

        // Redirige a la vista del carrito
        return redirect()->route('ver_carrito'); 
    }

    public function aumentar_cantidad($rowid) {
        $cart = \Config\Services::cart();
        $items = $cart->contents();

        if (!isset($items[$rowid])) {
            return redirect()->back()->with('msj', 'Item no encontrado en el carrito');
        }

        $item = $items[$rowid];

        if ($item['qty'] >= $item['stockLibro']) {
            return redirect()->back()->with('msj', 'No hay más stock disponible');
        }

        $cart->update([
            'rowid' => $rowid,
            'qty'   => $item['qty'] + 1,
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'stockLibro' => $item['stockLibro']
        ]);

        return redirect()->back()->with('mensaje', 'Cantidad actualizada');
    }


    public function disminuir_cantidad($rowid) {
        $cart = \Config\Services::cart();
        $items = $cart->contents();

        if (!isset($items[$rowid])) {
            return redirect()->back()->with('msj', 'Item no encontrado en el carrito');
        }

        $item = $items[$rowid];

        if ($item['qty'] <= 1) {
            return redirect()->back()->with('msj', 'No puedes tener menos de 1 producto');
        }

        $cart->update([
            'rowid' => $rowid,
            'qty'   => $item['qty'] - 1,
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'stockLibro' => $item['stockLibro']
        ]);

        return redirect()->back()->with('mensaje', 'Cantidad actualizada');
    }

    public function borrar ($rowid = null) {
        $cart = \Config\Services::cart();

        if ($rowid !== null) {
            $cart->remove($rowid);
        }

        return redirect()->route('ver_carrito')->with('mensaje', 'Libro eliminado correctamente!');
    }

    public function borrar_todo() {
        $cart = \Config\Services::cart();
        $cart->destroy(); 

        return redirect()->route('ver_carrito')->with('mensaje', 'El carrito se vació correctamente!');
    }

    public function procesar_finalizar_compra() {
        $cart = \Config\Services::cart();
        $venta = new venta_model();
        $detalle = new detalle_venta_model();
        $libros = new libros_model();
        $personaModel = new persona_model();
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();

        // Obtener datos del carrito
        $cartItems = $cart->contents();
        if (empty($cartItems)) {
            return redirect()->route('ver_carrito')->with('msj', 'Tu carrito está vacío. Agrega productos antes de finalizar la compra.');
        }

        //Obtener forma de envío y pago del FORMULARIO DEL MODAL (campos hidden)
        $formaEnvio = $request->getPost('selectedFormaEnvio');
        $formaPago = $request->getPost('selectedFormaPago');

        if (empty($formaPago)) { 
             return redirect()->route('ver_carrito')->with('msj', 'Debe seleccionar una forma de pago.');
        }

        
        $rules = [
            'selectedFormaEnvio' => 'required',
            'selectedFormaPago' => 'required',
            'telefono'  => 'required|numeric|min_length[8]|max_length[15]',
        ];
        $messages = [
            'telefono' => [
                'required'   => 'El teléfono es obligatorio',
                'numeric'    => 'Solo se permiten números',
                'min_length' => 'Debe tener al menos 8 dígitos',
                'max_length' => 'No puede superar los 15 dígitos',
            ],
            'selectedFormaEnvio' => 'Debe seleccionar una forma de envío.',
            'selectedFormaPago' => 'Debe seleccionar una forma de pago.',
        ];

        //Reglas condicionales para ENVÍO A DOMICILIO
        if ($formaEnvio == '2') { // '2' = Envío a domicilio
            $rules['domicilio'] = 'required|min_length[5]|max_length[100]';
            $rules['ciudad'] = 'required|alpha_space|min_length[2]|max_length[50]';
            $rules['provincia'] = 'required|alpha_space|min_length[2]|max_length[50]';

            $messages['domicilio'] = [
                'required' => 'El domicilio es obligatorio.',
                'min_length' => 'El domicilio debe tener al menos 5 caracteres.',
                'max_length' => 'El domicilio no puede superar los 100 caracteres.',
            ];
            $messages['ciudad'] = [
                'required' => 'La ciudad es obligatoria.',
                'alpha_space' => 'Solo se permiten letras y espacios en la ciudad.',
                'min_length' => 'La ciudad debe tener al menos 2 caracteres.',
                'max_length' => 'La ciudad no puede superar los 50 caracteres.',
            ];
            $messages['provincia'] = [
                'required' => 'La provincia es obligatoria.',
                'alpha_space' => 'Solo se permiten letras y espacios en la provincia.',
                'min_length' => 'La provincia debe tener al menos 2 caracteres.',
                'max_length' => 'La provincia no puede superar los 50 caracteres.',
            ];
        }

        // Reglas condicionales para PAGO CON TARJETA
        if ($formaPago == '2') { // '2' = Tarjeta 
            $rules['tarjeta'] = 'required|numeric|exact_length[16]';
            $rules['vencimiento'] = 'required'; 
            $rules['cvv'] = 'required|numeric|min_length[3]|max_length[4]';

            $messages['tarjeta'] = [
                'required' => 'El número de tarjeta es obligatorio.',
                'numeric' => 'El número de tarjeta debe ser numérico.',
                'exact_length' => 'El número de tarjeta debe tener 16 dígitos.',
            ];
            $messages['vencimiento'] = [
                'required' => 'La fecha de vencimiento es obligatoria.',
            ];
            $messages['cvv'] = [
                'required' => 'El CVV es obligatorio.',
                'numeric' => 'El CVV debe ser numérico.',
                'min_length' => 'El CVV debe tener al menos 3 dígitos.',
                'max_length' => 'El CVV debe tener como máximo 4 dígitos.',
            ];
        }

        $validation->setRules($rules, $messages);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Actualizar datos personales si se proporcionaron
        if ($formaEnvio == '2') { // Si fue envío a domicilio, actualizamos los datos
            $personaModel->update(session('id'), [
                'direccion' => $request->getPost('domicilio'),
                'ciudad'    => $request->getPost('ciudad'),
                'provincia' => $request->getPost('provincia'),
                'telefono'  => $request->getPost('telefono') 
            ]);
        } else { // Si fue retiro en sucursal, solo actualizar teléfono si es diferente
             $currentPersona = $personaModel->where('idPersona', session('id'))->first();
             if($currentPersona['telefono'] != $request->getPost('telefono')){
                $personaModel->update(session('id'), [
                    'telefono' => $request->getPost('telefono')
                ]);
             }
        }


        // TRANSACCIONES EN LA BASE DE DATOS
        $libros->db->transStart();

        try {
            //Re-verificación de stock final antes de procesar la compra 
            foreach ($cartItems as $item) {
                $libro = $libros->where('idLibro', $item['id'])->first();

                if (!$libro || $libro['stockLibro'] < $item['qty']) {
                    $libros->db->transRollback();
                    return redirect()->route('ver_carrito')->with('msj', 'Lo sentimos, el stock de "' . esc($item['name']) . '" ya no esta disponible. Por favor, revisa tu carrito.');
                }
            }

            //Guardar la venta principal
            $dataVenta = array(
                'idCliente' => session('id'),
                'fecha' => date('Y-m-d'),
                'formaPago' => $formaPago, 
                'total' => $cart->total(),
                'estado' => 'Pendiente' 
            );
            $venta_id = $venta->insert($dataVenta);

            if ($venta_id === false) {
                throw new \Exception("Error al insertar la venta.");
            }

            //Guardar los detalles de la venta y actualizar el stock
            foreach ($cartItems as $item) {
                $detalle_venta = array(
                    'idVenta'        => $venta_id,
                    'idLibro'        => $item['id'],
                    'cantidad'       => $item['qty'],
                    'precioUnitario' => $item['price']
                );

                $detalle->insert($detalle_venta);

                // Actualizar stock del libro
                $libros->update($item['id'], ['stockLibro' => $libro['stockLibro'] - $item['qty']]);
            }

            // Si todo sale bien, confirmar la transacción
            $libros->db->transComplete();

            //Vaciar el carrito
            $cart->destroy();

            //Redirige a una página de agradecimiento
            return redirect()->to(base_url("gracias_por_tu_compra"))->with('mensaje', '¡Compra realizada con éxito!');

        } catch (\Exception $e) {
            // Algo salió mal, asegurar el rollback
            $libros->db->transRollback();
            // Loguear el error para depuración
            log_message('error', 'Error en procesar_finalizar_compra: ' . $e->getMessage() . ' - Stack Trace: ' . $e->getTraceAsString());
            return redirect()->route('ver_carrito')->with('msj', 'Hubo un error al procesar tu compra. Por favor, inténtalo de nuevo más tarde.');
        }
    }

    public function gracias_por_tu_compra() {
        $data['titulo'] = '¡Compra Realizada!';
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/gracias_compra').
            view('plantilla/footer_view');
    }

    function gestionar_ventas() {
        $venta_Model = new venta_model();
        $detalle_Model = new detalle_venta_model();
        $cliente = new persona_model();
        $pago = new formapago_model();

        $data['persona'] = $cliente->findAll();
        $data['formapago'] = $pago->findAll();

        // Traer todas las ventas con joins
        $ventas = $venta_Model
            ->join('persona', 'persona.idPersona = venta.idCliente')
            ->join('formapago', 'formapago.idPago = venta.formaPago')
            ->findAll();

        // Ahora obtenemos los detalles de todas las ventas (evitar muchas consultas)
        $detalles = $detalle_Model->join('libros', 'libros.idLibro = detalleventa.idLibro')->findAll();

        // Agrupar detalles por idVenta
        $detallesPorVenta = [];
        foreach ($detalles as $detalle) {
            $detallesPorVenta[$detalle['idVenta']][] = $detalle;
        }

        $data['venta'] = $ventas;
        $data['detallesPorVenta'] = $detallesPorVenta; // pasar los detalles agrupados
        $data['titulo'] = 'Listar ventas';

        return view('plantilla/nav_admin_view', $data).
            view('backend/ventas').
            view('plantilla/footer_admin_view');
    }

    public function detalle_venta($idVenta) {
        $detalleModel = new detalle_venta_model();
        $libroModel = new libros_model();

        $detalles = $detalleModel->where('idVenta', $idVenta)->findAll();

        if (empty($detalles)) {
            return 'No hay detalles para esta venta.';
        }

        $html = '<ul class="list-group">';
        foreach ($detalles as $detalle) {
            $libro = $libroModel->find($detalle['idLibro']);
            $nombreLibro = $libro ? $libro['nombreLibro'] : 'Libro desconocido';
            $html .= '<li class="list-group-item">';
            $html .= 'Libro: '.'<strong>' . esc($nombreLibro) . '</strong> - Cantidad:' . $detalle['cantidad'] . ' - $' . $detalle['precioUnitario'];
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }


    public function estado_finalizado($id=null) {
        $data = array('estado'=>'Finalizado');
        $venta = new venta_model();
        $venta->update($id, $data);
        return redirect()->route('gestionar_ventas');
    }

}