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

$routes->get('carrito', 'Home::carro');

$routes->get('catalogo', 'Home::libros');

//RUTAS DE USUARIOCONTROLLER

$routes->post('registro_usuario', 'UsuarioController::add_cliente');

$routes->get('login', 'UsuarioController::login');

$routes->post('verificar_usuario', 'UsuarioController::buscar_usuario');

$routes->get('logout', 'UsuarioController::cerrar_sesion');

$routes->get('user_admin', 'UsuarioController::admin');

$routes->post('consulta', 'UsuarioController::add_consulta');

$routes->get('consultas', 'UsuarioController::admin');

$routes->get('responder/(:num)', 'UsuarioController::responder/$1');

$routes->get('eliminarConsulta/(:num)', 'UsuarioController::eliminar/$1');

//RUTAS DE LIBROCONTROLLER

$routes->get('agregar', 'LibroController::form_agregar_libro');

$routes->post('insertar_libro', 'LibroController::registrar_libro');

$routes->get('gestionar', 'LibroController::gestionar_libros');

$routes->get('editar/(:num)', 'LibroController::editar_libro/$1');

$routes->post('actualizar', 'LibroController::actualizar_libro');

$routes->get('eliminar/(:num)', 'LibroController::eliminar_libro/$1');

$routes->get('activar/(:num)', 'LibroController::activar_libro/$1');

$routes->get('productos', 'LibroController::listar_libros');

$routes->get('producto', 'LibroController::index');

$routes->get('/', 'LibroController::inicio');

$routes->get('buscar', 'LibroController::buscar');

$routes->get('buscar_admin', 'LibroController::buscar_admin');

$routes->get('por_categoria', 'LibroController::listar_libros_admin');

//RUTAS DE CARRITOCONTROLLER

$routes->get('ver_carrito', 'CarritoController::ver_carrito');

$routes->post('add_cart', 'CarritoController::agregar_carrito');

$routes->get('aumentar_cantidad/(:any)', 'CarritoController::aumentar_cantidad/$1');

$routes->get('disminuir_cantidad/(:any)', 'CarritoController::disminuir_cantidad/$1');

$routes->get('eliminar_item/(:any)', 'CarritoController::borrar/$1');

$routes->get('vaciar_carrito/(:any)', 'CarritoController::borrar_todo/$1');

$routes->post('procesar_finalizar_compra', 'CarritoController::procesar_finalizar_compra');

$routes->get('gracias_por_tu_compra', 'CarritoController::gracias_por_tu_compra');

$routes->get('gestionar_ventas', 'CarritoController::gestionar_ventas');

$routes->get('finalizado/(:num)', 'CarritoController::estado_finalizado/$1');

$routes->get('detalle_venta/(:num)', 'CarritoController::detalle_venta/$1');

