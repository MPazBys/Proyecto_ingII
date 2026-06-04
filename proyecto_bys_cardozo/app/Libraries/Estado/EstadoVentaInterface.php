<?php

namespace App\Libraries\Estado;

use App\Controllers\VentaController;

/**
 * Interfaz EstadoVentaInterface
 * 
 * Define el contrato obligatorio para todas las clases de estado de una venta
 * (Pendiente, Enviado, Finalizado). Cada estado concreto implementará estos métodos
 * para controlar su propio comportamiento y sus transiciones.
 */
interface EstadoVentaInterface 
{
    /**
     * Valida si es permitido pasar desde el estado actual hacia un nuevo estado.
     * Aplica de forma estricta las reglas de la máquina de estados según el tipo de despacho.
     *
     * @param array $venta Datos actuales de la venta registrados en la base de datos.
     * @param string $nuevoEstado El nombre del estado al que se intenta cambiar.
     * @param int $formaEnvio Tipo de despacho (1 = Retiro en sucursal, 2 = Envío a domicilio).
     * @return bool True si la transición es válida y permitida, False en caso contrario.
     */
    public function cambiarEstado(array $venta, string $nuevoEstado, int $formaEnvio): bool;

    /**
     * Ejecuta las acciones automáticas secundarias una vez consolidado el cambio de estado.
     * Centraliza comportamientos colaterales específicos de cada fase, como por ejemplo,
     * el armado y despacho de notificaciones por correo electrónico al cliente (SMTP).
     *
     * @param array $venta Datos de la venta actualizada.
     * @param array $cliente Datos del usuario/cliente que realizó la compra.
     * @return void
     */
    public function ejecutarAccionPostTransicion(array $venta, array $cliente): void;

    /**
     * Devuelve el nombre identificatorio del estado actual.
     *
     * @return string Nombre del estado (ej: 'Pendiente', 'Enviado', 'Finalizado').
     */
    public function getNombre(): string;
}