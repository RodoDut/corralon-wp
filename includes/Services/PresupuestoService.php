<?php

declare(strict_types=1);

namespace RDT\Corralon\Services;

use RDT\Corralon\Domain\LineaPresupuesto;
use RDT\Corralon\Domain\SolicitudPresupuesto;
use RDT\Corralon\Mail\MailerInterface;
use RDT\Corralon\Repositories\CarritoRepositoryInterface;
use RDT\Corralon\Repositories\PresupuestoRepositoryInterface;

class PresupuestoService
{
    public function __construct(
        private readonly CarritoRepositoryInterface     $carrito,
        private readonly MailerInterface                $mailer,
        private readonly string                         $emailDestino,
        private readonly PresupuestoRepositoryInterface $presupuestoRepo,
    ) {}

    public function enviar(SolicitudPresupuesto $solicitud): bool
    {
        $lineas = $this->carrito->getLineas();

        $this->presupuestoRepo->guardar($solicitud, $lineas);

        $asunto = 'Nueva solicitud de presupuesto de ' . $solicitud->nombre;
        $cuerpo = $this->buildEmailBody($solicitud, $lineas);

        return $this->mailer->send($this->emailDestino, $asunto, $cuerpo);
    }

    /** @param LineaPresupuesto[] $lineas */
    private function buildEmailBody(SolicitudPresupuesto $solicitud, array $lineas): string
    {
        $filas = '';
        $total = 0.0;

        foreach ($lineas as $linea) {
            $precio   = number_format($linea->precioUnitario, 2, ',', '.');
            $subtotal = number_format($linea->subtotal, 2, ',', '.');
            $total   += $linea->subtotal;
            $nombre   = htmlspecialchars($linea->nombre, ENT_QUOTES);

            $filas .= "
                <tr>
                    <td>{$nombre}</td>
                    <td style='text-align:center'>{$linea->cantidad}</td>
                    <td style='text-align:right'>\${$precio}</td>
                    <td style='text-align:right'>\${$subtotal}</td>
                </tr>";
        }

        $totalFmt = number_format($total, 2, ',', '.');
        $mensaje  = $solicitud->mensaje !== ''
            ? nl2br(htmlspecialchars($solicitud->mensaje, ENT_QUOTES))
            : '—';

        $nombre   = htmlspecialchars($solicitud->nombre, ENT_QUOTES);
        $email    = htmlspecialchars($solicitud->email, ENT_QUOTES);
        $telefono = htmlspecialchars($solicitud->telefono, ENT_QUOTES);

        $sinProductos = $lineas === []
            ? '<p><em>El carrito no tenía productos al momento del envío.</em></p>'
            : '';

        return "
        <h2>Nueva solicitud de presupuesto</h2>
        <p><strong>Nombre:</strong> {$nombre}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Teléfono:</strong> {$telefono}</p>
        <p><strong>Mensaje:</strong> {$mensaje}</p>
        <h3>Detalle del carrito</h3>
        {$sinProductos}
        <table border='1' cellpadding='6' cellspacing='0'
               style='border-collapse:collapse;width:100%;max-width:600px'>
            <thead style='background:#f5f5f5'>
                <tr>
                    <th style='text-align:left'>Producto</th>
                    <th>Cant.</th>
                    <th style='text-align:right'>Precio unit.</th>
                    <th style='text-align:right'>Subtotal</th>
                </tr>
            </thead>
            <tbody>{$filas}</tbody>
            <tfoot>
                <tr>
                    <td colspan='3' style='text-align:right'><strong>Total estimado:</strong></td>
                    <td style='text-align:right'><strong>\${$totalFmt}</strong></td>
                </tr>
            </tfoot>
        </table>
        ";
    }
}
