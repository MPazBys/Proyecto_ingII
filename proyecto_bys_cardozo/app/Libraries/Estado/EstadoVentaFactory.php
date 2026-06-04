<?php

namespace App\Libraries\Estado;

class EstadoVentaFactory 
{
    /**
     * Instancia y devuelve el objeto de estado correspondiente.
     * * @param string $estado Nombre del estado (ej: 'Pendiente', 'Enviado', 'Finalizado').
     * @return EstadoVentaInterface Objeto concreto que implementa la interfaz de estado.
     * @throws \InvalidArgumentException Si el estado proporcionado no existe en el sistema.
     */
    public static function crear(string $estado): EstadoVentaInterface 
    {
        // Normaliza el texto y mapea cada string con su respectiva clase lógica
        return match (ucfirst(strtolower($estado))) {
            'Pendiente'  => new EstadoPendiente(),
            'Enviado'    => new EstadoEnviado(),
            'Finalizado' => new EstadoFinalizado(),
            default      => throw new \InvalidArgumentException("Estado no válido: $estado"),
        };
    }
}