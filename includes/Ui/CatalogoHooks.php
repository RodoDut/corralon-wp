<?php

declare(strict_types=1);

namespace RDT\Corralon\Ui;

use RDT\Corralon\Repositories\ProductoRepositoryInterface;

class CatalogoHooks
{
    public function __construct(
        private readonly ProductoRepositoryInterface $repository,
    ) {}

    public function register(): void
    {
        add_shortcode('catalogo_productos', [$this, 'renderShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function renderShortcode(array $atts): string
    {
        return '<div id="catalogo-root">'
            . '<nav id="catalogo-tabs" role="tablist" aria-label="Filtrar por categoría"></nav>'
            . '<div id="catalogo-grilla"></div>'
            . '<div id="catalogo-spinner" hidden aria-live="polite">'
            .     '<span class="catalogo-spinner-icon" aria-hidden="true"></span>'
            . '</div>'
            . '<div id="catalogo-sentinel" aria-hidden="true" hidden></div>'
            . '</div>';
    }

    public function enqueueAssets(): void
    {
        global $post;

        if (
            !is_singular()
            || !($post instanceof \WP_Post)
            || !has_shortcode($post->post_content, 'catalogo_productos')
        ) {
            return;
        }

        wp_enqueue_style(
            'rdt-catalogo',
            RDT_CORRALON_URL . 'assets/css/catalogo.css',
            [],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/css/catalogo.css' )
        );

        wp_register_script(
            'rdt-carrito',
            RDT_CORRALON_URL . 'assets/js/carrito.js',
            [],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/js/carrito.js' ),
            true
        );

        wp_enqueue_script(
            'rdt-catalogo',
            RDT_CORRALON_URL . 'assets/js/catalogo.js',
            ['rdt-carrito'],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/js/catalogo.js' ),
            true
        );

        $categorias = $this->repository->getCategorias();
        $cartUrl    = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '/carrito/';

        wp_localize_script('rdt-catalogo', 'rdtCatalogo', [
            'rest_url'   => rest_url('corralon/v1/catalogo'),
            'nonce'      => wp_create_nonce('wp_rest'),
            'cart_nonce' => wp_create_nonce('wc_store_api'),
            'cart_url'   => $cartUrl,
            'por_pagina' => 12,
            'categorias' => $categorias,
        ]);
    }
}
