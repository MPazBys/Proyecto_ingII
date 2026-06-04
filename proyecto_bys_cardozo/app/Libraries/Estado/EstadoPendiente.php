<?php

namespace App\Libraries\Estado;

/**
 * Clase EstadoPendiente
 * * Representa la fase inicial o de recepción en el ciclo de vida de un pedido.
 * Al ser el punto de partida, controla las transiciones iniciales permitiendo el 
 * avance secuencial hacia el despacho o el salto directo al cierre de la venta, 
 * según la modalidad de entrega seleccionada por el cliente.
 */
class EstadoPendiente implements EstadoVentaInterface 
{
    public function getNombre(): string { return 'Pendiente'; }

    public function cambiarEstado(array $venta, string $nuevoEstado, int $formaEnvio): bool 
    {
        // Si es retiro presencial (1), va directo a Finalizado. Si es envío (2), va a Enviado.
        if ($formaEnvio === 1 && $nuevoEstado === 'Finalizado') {
            return true;
        }
        if ($formaEnvio === 2 && $nuevoEstado === 'Enviado') {
            return true;
        }
        return false;
    }

    public function ejecutarAccionPostTransicion(array $venta, array $cliente): void 
    {
        // Al estar pendiente, ya se envió el comprobante inicial en el carrito.
    }
}