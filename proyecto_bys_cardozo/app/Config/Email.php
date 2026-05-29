<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuración del Servicio de Correos Electrónicos.
 * Configurado centralmente para despachar notificaciones reales a internet vía SMTP de Gmail.
 */
class Email extends BaseConfig
{
    // Dirección que verá el cliente como remitente (Debe coincidir con tu SMTPUser)
    public string $fromEmail  = 'myp.libros@gmail.com'; 
    public string $fromName   = 'Librería M&P - Soporte';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * Protocolo de envío de correos: smtp (Obligatorio para servidores externos)
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * Servidor SMTP de Google
     */
    public string $SMTPHost = 'smtp.gmail.com';

    /**
     * Tu cuenta de Gmail institucional o centralizada para el proyecto
     */
    public string $SMTPUser = 'myp.libros@gmail.com';

    /**
     * Contraseña de Aplicación de 16 caracteres generada desde Google (SIN espacios)
     */
    public string $SMTPPass = 'jultsakvqphszbhv';

    /**
     * Puerto SMTP seguro para conexiones implícitas SSL
     */
    public int $SMTPPort = 465;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * Encriptación SSL obligatoria para conectar con el puerto 465 de Gmail
     */
    public string $SMTPCrypto = 'ssl';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Tipo de correo en formato 'html' para soportar plantillas estilizadas
     */
    public string $mailType = 'html';

    /**
     * Codificación universal de caracteres para evitar problemas con eñes o acentos
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Caracteres de nueva línea requeridos por los estándares RFC 822
     */
    public string $CRLF = "\r\n";

    /**
     * Caracteres de nueva línea requeridos por los estándares RFC 822
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;
}