<?php

declare(strict_types=1);

namespace RDT\Corralon\Ui;

/**
 * Ajustes de integración con WooCommerce.
 *
 * Responsabilidad: redirigir los enlaces nativos de WooCommerce
 * ("Ir a Tienda", botones del carrito vacío, etc.) hacia el
 * catálogo custom del plugin en lugar de la shop page de WC.
 */
class WoocommerceHooks
{
    public function __construct(
        private readonly string $catalogoUrl,
    ) {}

    public function register(): void
    {
        // Reemplaza la URL de la shop page de WooCommerce
        add_filter('woocommerce_get_shop_url', [$this, 'redirectToCustomCatalogo']);

        // También aplica al botón "Ir a Tienda" del carrito vacío
        add_filter('woocommerce_return_to_shop_redirect', [$this, 'redirectToCustomCatalogo']);
    }

    public function redirectToCustomCatalogo(): string
    {
        return $this->catalogoUrl;
    }
}
