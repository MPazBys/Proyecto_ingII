<?php

namespace App\Libraries\Estado;

/**
 * Clase Abstracta EstadoVenta
 * * Funciona como base y paraguas contenedor para todas las fases de una venta.
 * Centraliza el mapa de clases disponibles para la instanciación dinámica
 * y define el contrato que cada estado concreto debe implementar obligatoriamente.
 */
abstract class EstadoVenta 
{
    /**
     * Mapa estático.
     * Vincula el string plano proveniente de la base de datos con el Tipo de Dato real
     * de su clase correspondiente usando la directiva ::class, lo que permite la instanciación dinámica
     * de objetos de estado sin necesidad de condicionales o hardcodeos, facilitando la escalabilidad y el 
     * mantenimiento del código.
     */
    public const MAPA = [
        'pendiente'  => EstadoPendiente::class,
        'enviado'    => EstadoEnviado::class,
        'finalizado' => EstadoFinalizado::class,
    ];

    /**
     * Valida si es permitido pasar desde el estado actual hacia un nuevo estado.
     * Aplica de forma estricta las reglas de la máquina de estados según el tipo de despacho.
     *
     * @param array $venta Datos actuales de la venta registrados en la base de datos.
     * @param string $nuevoEstado El nombre del estado al que se intenta cambiar.
     * @param int $formaEnvio Tipo de despacho (1 = Retiro en sucursal, 2 = Envío a domicilio).
     * @return bool True si la transición es válida y permitida, False en caso contrario.
     */
    abstract public function cambiarEstado(array $venta, string $nuevoEstado, int $formaEnvio): bool;

    /**
     * Ejecuta las acciones automáticas secundarias una vez consolidado el cambio de estado.
     * Centraliza comportamientos colaterales específicos de cada fase, como por ejemplo,
     * el armado y despacho de notificaciones por correo electrónico al cliente (SMTP).
     *
     * @param array $venta Datos de la venta actualizada.
     * @param array $cliente Datos del usuario/cliente que realizó la compra.
     * @return void
     */
    abstract public function ejecutarAccionPostTransicion(array $venta, array $cliente): void;

    /**
     * Devuelve el nombre identificatorio del estado actual.
     *
     * @return string Nombre del estado (ej: 'Pendiente', 'Enviado', 'Finalizado').
     */
    abstract public function getNombre(): string;
}