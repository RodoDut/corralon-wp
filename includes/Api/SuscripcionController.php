<?php

declare(strict_types=1);

namespace RDT\Corralon\Api;

use RDT\Corralon\Mail\SuscripcionMailer;
use WP_REST_Request;
use WP_REST_Response;

class SuscripcionController
{
    private const API_NAMESPACE = 'corralon/v1';
    private const ROUTE         = '/suscripcion';
    private const OPTION_KEY    = 'rdt_corralon_suscriptores';

    public function __construct(
        private readonly SuscripcionMailer $mailer,
    ) {}

    public function registerRoutes(): void
    {
        register_rest_route(self::API_NAMESPACE, self::ROUTE, [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle'],
            'permission_callback' => '__return_true',
            'args'                => [
                'email' => [
                    'type'     => 'string',
                    'required' => true,
                    'format'   => 'email',
                ],
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $email = sanitize_email((string) $request->get_param('email'));

        if (!is_email($email)) {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Email inválido.'],
                422
            );
        }

        // ── A) Email ya registrado como usuario WordPress ──────────────
        $user_id = email_exists($email);
        if ($user_id !== false) {
            $user = get_user_by('id', $user_id);
            if (
                $user instanceof \WP_User
                && (
                    in_array('subscriber', (array) $user->roles, true)
                    || in_array('rdt_suscriptor', (array) $user->roles, true)
                )
            ) {
                return new WP_REST_Response(
                    ['success' => true, 'ya_existia' => true],
                    200
                );
            }
            // Email existe con otro rol — no revelamos datos, tratamos como ya existente
            return new WP_REST_Response(
                ['success' => true, 'ya_existia' => true],
                200
            );
        }

        // ── B) Crear nuevo usuario WordPress ──────────────────────────
        $username_base = sanitize_user((string) strstr($email, '@', true), true);
        if ($username_base === '') {
            $username_base = sanitize_user($email, true);
        }

        // Evitar conflicto de username
        $username = $username_base;
        if (username_exists($username)) {
            $i = 1;
            while (username_exists($username . $i)) {
                $i++;
            }
            $username = $username . $i;
        }

        $result = wp_create_user($username, wp_generate_password(), $email);

        if ($result instanceof \WP_Error) {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Error al registrar.'],
                500
            );
        }

        $new_user_id = (int) $result;
        $user        = new \WP_User($new_user_id);
        $user->set_role('subscriber');
        update_user_meta($new_user_id, 'rdt_suscriptor', '1');

        $this->mailer->notificarSuscriptor($email);
        $this->mailer->notificarAdmin($email);

        // ── C) Respaldo en wp_options ─────────────────────────────────
        $suscriptores   = (array) get_option(self::OPTION_KEY, []);
        $suscriptores[] = [
            'email' => $email,
            'fecha' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];
        update_option(self::OPTION_KEY, $suscriptores, false);

        return new WP_REST_Response(
            ['success' => true, 'ya_existia' => false],
            201
        );
    }
}
