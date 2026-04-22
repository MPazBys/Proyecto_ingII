<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data['titulo'] = "Bienvenidos"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/inicio').
            view('plantilla/footer_view');
    }

    public function somos()
    {
        $data['titulo'] = "Quienes Somos"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/nosotros').
            view('plantilla/footer_view');
    }

    public function comercio()
    {
        $data['titulo'] = "Informacion"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/comercializacion').
            view('plantilla/footer_view');
    }

    public function contacto()
    {
        $data['titulo'] = "Contactos"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/contactos').
            view('plantilla/footer_view');
    }

    public function terminos()
    {
        $data['titulo'] = "Terminos y usos"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/terminos_usos').
            view('plantilla/footer_view');
    }

    public function acceso()
    {
        $data['titulo'] = "Iniciar Sesion"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/login').
            view('plantilla/footer_view');
    }

    public function crearcuenta()
    {
        $data['titulo'] = "Registro"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/registro').
            view('plantilla/footer_view');
    }

    public function carro()
    {
        $data['titulo'] = "Carrito"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/carrito').
            view('plantilla/footer_view');
    }

    public function libros()
    {
        $data['titulo'] = "Catalogo"; 
        return view('plantilla/header_view', $data).
            view('plantilla/nav_view').
            view('contenido/catalogo').
            view('plantilla/footer_view');
    }

}

