<?php

declare(strict_types=1);

namespace RDT\Corralon\Admin;

use RDT\Corralon\Domain\PresupuestoEstado;
use RDT\Corralon\Repositories\PresupuestoRepositoryInterface;

class AdminPresupuestosController
{
    private const SLUG = 'corralon-presupuestos';

    public function __construct(
        private readonly PresupuestoRepositoryInterface $repository,
    ) {}

    public function register(): void
    {
        add_action('admin_init', [$this, 'handle']);
    }

    public function handle(): void
    {
        if (empty($_POST['rdt_presupuesto_accion']) || empty($_POST['presupuesto_id'])) {
            return;
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso para realizar esta acción.', 'corralon-materiales'));
        }

        $id     = (int) $_POST['presupuesto_id'];
        $accion = sanitize_key($_POST['rdt_presupuesto_accion']);

        check_admin_referer('rdt_presupuesto_accion_' . $id, 'rdt_nonce');

        if ($accion === 'guardar_estado') {
            $estado = sanitize_key((string) ($_POST['rdt_estado'] ?? ''));
            if (in_array($estado, [PresupuestoEstado::PENDIENTE, PresupuestoEstado::RESPONDIDO], true)) {
                $this->repository->actualizarEstado($id, $estado);
            }
        } elseif ($accion === 'guardar_nota') {
            $nota = sanitize_textarea_field((string) ($_POST['rdt_nota'] ?? ''));
            $this->repository->actualizarNota($id, $nota);
        }

        wp_safe_redirect(
            admin_url('admin.php?page=' . self::SLUG . '&id=' . $id . '&updated=1')
        );
        exit;
    }
}
