<?php

declare(strict_types=1);

namespace RDT\Corralon\Api;

use RDT\Corralon\Domain\SolicitudPresupuesto;
use RDT\Corralon\Services\PresupuestoService;
use WP_REST_Request;
use WP_REST_Response;

class PresupuestoController
{
    private const API_NAMESPACE = 'rdt/v1';
    private const ROUTE         = '/presupuesto';

    public function __construct(
        private readonly PresupuestoService $service,
    ) {}

    public function registerRoutes(): void
    {
        register_rest_route(self::API_NAMESPACE, self::ROUTE, [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $nombre   = sanitize_text_field((string) ($request->get_param('nombre')   ?? ''));
        $email    = sanitize_email((string)       ($request->get_param('email')    ?? ''));
        $telefono = sanitize_text_field((string)  ($request->get_param('telefono') ?? ''));
        $mensaje  = sanitize_textarea_field((string) ($request->get_param('mensaje') ?? ''));

        if ($nombre === '' || $email === '' || $telefono === '') {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Nombre, email y teléfono son obligatorios.'],
                400
            );
        }

        if (!is_email($email)) {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'El email no tiene un formato válido.'],
                400
            );
        }

        $solicitud = new SolicitudPresupuesto($nombre, $email, $telefono, $mensaje);
        $enviado   = $this->service->enviar($solicitud);

        if (!$enviado) {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'No se pudo enviar el presupuesto. Intentá nuevamente.'],
                500
            );
        }

        return new WP_REST_Response(['success' => true], 200);
    }
}
