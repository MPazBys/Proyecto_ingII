<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('nosotros', 'Home::somos');

$routes->get('comercializacion', 'Home::comercio');

$routes->get('contactos', 'Home::contacto');

$routes->get('terminos_usos', 'Home::terminos');

$routes->get('login', 'Home::acceso');

$routes->get('registro', 'Home::crearcuenta');

$routes->get('carrito', 'CarritoController::ver_carrito');

$routes->get('catalogo', 'Home::libros');

//RUTAS DE AUTENTICACIÓN Y CONSULTAS
$routes->post('registro_usuario', 'AuthController::add_cliente');
$routes->post('verificar_usuario', 'AuthController::buscar_usuario');
$routes->get('logout', 'AuthController::cerrar_sesion');

$routes->post('consulta', 'ConsultaController::add_consulta');
$routes->get('user_admin', 'ConsultaController::admin');
$routes->get('consultas', 'ConsultaController::admin');
$routes->get('responder/(:num)', 'ConsultaController::responder/$1');
$routes->post('procesar_respuesta', 'ConsultaController::procesar_respuesta');

//RUTAS DE LIBROCONTROLLER

$routes->get('agregar', 'LibroController::formulario');

$routes->post('insertar_libro', 'LibroController::registrar_libro');

$routes->get('gestionar', 'LibroController::index/listar_libros');

$routes->get('editar/(:num)', 'LibroController::formulario/$1');

$routes->post('actualizar', 'LibroController::actualizar_libro');

$routes->get('eliminar/(:num)', 'LibroController::cambiar_estado/$1/0');

$routes->get('activar/(:num)', 'LibroController::cambiar_estado/$1/1');

$routes->get('productos', 'LibroController::listar');

$routes->get('producto', 'LibroController::index/productos');

$routes->get('/', 'LibroController::inicio');

$routes->get('buscar', 'LibroController::buscar');

$routes->get('buscar_admin', 'LibroController::buscar');

$routes->get('por_categoria', 'LibroController::listar');

//RUTAS DE CARRITOCONTROLLER

$routes->get('ver_carrito', 'CarritoController::ver_carrito');

$routes->post('add_cart', 'CarritoController::agregar_carrito');

$routes->get('aumentar_cantidad/(:any)', 'CarritoController::actualizar_cantidad/$1/aumentar');

$routes->get('disminuir_cantidad/(:any)', 'CarritoController::actualizar_cantidad/$1/disminuir');

$routes->get('eliminar_item/(:any)', 'CarritoController::eliminar_item/$1');

$routes->get('vaciar_carrito/(:any)', 'CarritoController::eliminar_item/$1');

$routes->post('procesar_finalizar_compra', 'CarritoController::procesar_finalizar_compra');

$routes->get('gracias_por_tu_compra', 'CarritoController::gracias_por_tu_compra');

$routes->get('gestionar_ventas', 'VentaController::gestionar_ventas');
$routes->get('detalle_venta/(:num)', 'VentaController::detalle_venta/$1');
$routes->get('cambiar_estado/(:num)/(:segment)', 'VentaController::cambiar_estado/$1/$2');

