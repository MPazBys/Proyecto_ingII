<?php

namespace App\Libraries\Estado;

/**
 * Clase EstadoEnviado
 * * Representa la fase intermedia de tránsito o distribución logística de un pedido.
 * Actúa como un estado de control exclusivo para la modalidad de envío a domicilio, 
 * encargándose de gestionar la transición hacia el cierre definitivo y de despachar 
 * las alertas de seguimiento en camino para el comprador.
 */
class EstadoEnviado extends EstadoVenta
{
    public function getNombre(): string { return 'Enviado'; }

    public function cambiarEstado(array $venta, string $nuevoEstado, int $formaEnvio): bool 
    {
        // Desde enviado solo se puede pasar a Finalizado si es envío a domicilio
        return ($formaEnvio === 2 && $nuevoEstado === 'Finalizado');
    }

    public function ejecutarAccionPostTransicion(array $venta, array $cliente): void 
    {
        // Lógica de notificación SMTP/mail al cliente informando que su pedido ha sido enviado
        $email = \Config\Services::email();
        $email->setTo($cliente['correoPersona']);
        $email->setSubject('¡Tu pedido está en camino! - Librería M&P');
        
        $html = '<h2>¡Hola ' . esc($cliente['nombrePersona']) . '!</h2>';
        $html .= '<p>Queremos informarte que tu pedido <strong>#' . $venta['idVenta'] . '</strong> ha sido despachado.</p>';
        
        $email->setMessage($html);
        $email->send();
    }
}