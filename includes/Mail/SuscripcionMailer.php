<?php

declare(strict_types=1);

namespace RDT\Corralon\Mail;

class SuscripcionMailer
{
    public function notificarSuscriptor(string $email): void
    {
        $asunto  = '¡Bienvenido a las ofertas de Corralón de Materiales!';
        $cuerpo  = '<p>¡Gracias por suscribirte!</p>'
                 . '<p>A partir de ahora recibirás nuestras mejores ofertas'
                 . ' y novedades directamente en tu correo.</p>'
                 . '<p>Equipo de Corralón de Materiales</p>';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($email, $asunto, $cuerpo, $headers);
    }

    public function notificarAdmin(string $email): void
    {
        $admin   = (string) get_option('admin_email');
        $asunto  = "Nuevo suscriptor: {$email}";
        $fecha   = current_time('d/m/Y H:i');
        $cuerpo  = "<p>Se registró un nuevo suscriptor: {$email}</p>"
                 . "<p>Fecha y hora: {$fecha}</p>";
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($admin, $asunto, $cuerpo, $headers);
    }
}
