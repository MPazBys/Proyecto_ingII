<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('/'));
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
        if (session('login') && !session('correo')) {
            $userModel = new \App\Models\persona_model();
            $user = $userModel->find(session('id'));
            if ($user) {
                session()->set('correo', $user['correoPersona']);
            }
        }
        return view('contenido/contactos', $data);
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
        return view('contenido/login', $data);
    }

    public function crearcuenta()
    {
        $data['titulo'] = "Registro"; 
        return view('contenido/registro', $data);
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

