<?php

declare(strict_types=1);

namespace RDT\Corralon\Ui;

use RDT\Corralon\Repositories\ProductoRepositoryInterface;

class ProductoDetalleHooks
{
    public function __construct(
        private readonly ProductoRepositoryInterface $repository,
    ) {}

    public function register(): void
    {
        add_filter('woocommerce_locate_template', [$this, 'overrideSingleProduct'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function overrideSingleProduct(string $template, string $template_name): string
    {
        if ($template_name === 'single-product.php') {
            return RDT_CORRALON_PATH . 'templates/single-product.php';
        }
        return $template;
    }

    public function enqueueAssets(): void
    {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }

        wp_enqueue_style(
            'rdt-barlow',
            'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'rdt-detalle',
            RDT_CORRALON_URL . 'assets/css/detalle.css',
            ['rdt-barlow'],
            (string) filemtime(RDT_CORRALON_PATH . 'assets/css/detalle.css')
        );

        wp_register_script(
            'rdt-carrito',
            RDT_CORRALON_URL . 'assets/js/carrito.js',
            [],
            (string) filemtime(RDT_CORRALON_PATH . 'assets/js/carrito.js'),
            true
        );

        wp_enqueue_script(
            'rdt-detalle',
            RDT_CORRALON_URL . 'assets/js/detalle.js',
            ['rdt-carrito'],
            (string) filemtime(RDT_CORRALON_PATH . 'assets/js/detalle.js'),
            true
        );

        $cartUrl = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '/carrito/';

        wp_localize_script('rdt-detalle', 'rdtDetalle', [
            'rest_url'   => rest_url('corralon/v1/catalogo'),
            'nonce'      => wp_create_nonce('wp_rest'),
            'cart_nonce' => wp_create_nonce('wc_store_api'),
            'cart_url'   => $cartUrl,
        ]);
    }
}
