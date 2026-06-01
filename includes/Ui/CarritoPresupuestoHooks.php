<?php

declare(strict_types=1);

namespace RDT\Corralon\Ui;

class CarritoPresupuestoHooks
{
    public function register(): void
    {
        add_action('woocommerce_proceed_to_checkout', [$this, 'renderBoton']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function renderBoton(): void
    {
        echo '<button type="button" id="rdt-solicitar-presupuesto" class="button alt checkout-button wp-element-button rdt-presupuesto-btn">'
            . esc_html__('Solicitar presupuesto', 'corralon-materiales')
            . '</button>';
    }

    public function enqueueAssets(): void
    {
        if (!function_exists('is_cart') || !is_cart()) {
            return;
        }

        wp_enqueue_style(
            'rdt-presupuesto',
            RDT_CORRALON_URL . 'assets/css/presupuesto.css',
            [],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/css/presupuesto.css' )
        );

        wp_enqueue_script(
            'rdt-presupuesto',
            RDT_CORRALON_URL . 'assets/js/presupuesto.js',
            ['jquery'],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/js/presupuesto.js' ),
            true
        );

        wp_localize_script('rdt-presupuesto', 'rdtPresupuesto', [
            'apiUrl' => rest_url('rdt/v1/presupuesto'),
            'nonce'  => wp_create_nonce('wp_rest'),
        ]);
    }
}
