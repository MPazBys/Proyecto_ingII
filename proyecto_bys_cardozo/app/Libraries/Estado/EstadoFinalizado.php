<?php

namespace App\Libraries\Estado;

/**
 * Clase EstadoFinalizado
 * * Representa la fase terminal en el ciclo de vida de un pedido.
 * Al ser un estado de cierre, bloquea cualquier transición posterior y despacha
 * la notificación de entrega o retiro definitivo de la compra.
 */

class EstadoFinalizado extends EstadoVenta
{
    public function getNombre(): string { return 'Finalizado'; }

    public function cambiarEstado(array $venta, string $nuevoEstado, int $formaEnvio): bool 
    {
        // Estado terminal, no permite más transiciones
        return false;
    }

    public function ejecutarAccionPostTransicion(array $venta, array $cliente): void 
    {
        $email = \Config\Services::email();
        $email->setTo($cliente['correoPersona']);
        
        $formaEnvio = (int)$venta['formaEnvio']; // 1 = Retiro, 2 = Domicilio
        $email->setSubject(($formaEnvio === 1) ? '¡Retiraste tu pedido con éxito!' : 'Tu pedido fue entregado con éxito');
        
        $html = '<h2>¡Hola ' . esc($cliente['nombrePersona']) . '!</h2>';
        $html .= '<p>Tu pedido <strong>#' . $venta['idVenta'] . '</strong> ha sido ' . (($formaEnvio === 1) ? 'retirado de nuestra sucursal' : 'entregado correctamente') . '.</p>';
        $html .= '<p>Esperamos que disfrutes de tu compra. Si tienes alguna consulta, no dudes en escribirnos.</p>';
        $html .= '<br><p>¡Muchas gracias por tu confianza!<br><strong>El equipo de M&P.</strong></p>';

        $email->setMessage($html);

        // Control y log de auditoría original si el motor de correo falla
        if (!$email->send()) {
            log_message('error', 'Error al despachar notificación SMTP para venta #' . $venta['idVenta']);
        }
    }
}