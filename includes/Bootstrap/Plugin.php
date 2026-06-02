<?php

declare(strict_types=1);

namespace RDT\Corralon\Bootstrap;

class Plugin
{
    public function register(): void
    {
        add_action( 'init', [ $this, 'onInit' ] );
        add_action( 'admin_menu', [ $this, 'onAdminMenu' ] );
        add_action( 'rest_api_init', [ $this, 'onRestApiInit' ] );

        // WooCommerce no inicializa la sesión en contexto REST por defecto.
        // Este hook fuerza la carga del carrito para que CarritoRepository
        // pueda leer los ítems correctamente al procesar solicitudes de presupuesto.
        add_filter( 'woocommerce_session_handler', static function ( $handler ) {
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                add_action( 'woocommerce_init', static function () {
                    if ( WC()->session && ! WC()->session->has_session() ) {
                        WC()->session->init();
                    }
                    WC()->cart->get_cart();
                } );
            }
            return $handler;
        } );
    }

    public function onInit(): void
    {
        \RDT\Corralon\CPT\PresupuestoCpt::register();

        $productoRepo    = new \RDT\Corralon\Repositories\ProductoRepository();
        $presupuestoRepo = new \RDT\Corralon\Repositories\PresupuestoRepository();

        ( new \RDT\Corralon\Ui\CarritoPresupuestoHooks() )->register();
        ( new \RDT\Corralon\Ui\CatalogoHooks( $productoRepo ) )->register();
        ( new \RDT\Corralon\Ui\ProductoDetalleHooks( $productoRepo ) )->register();
        ( new \RDT\Corralon\Admin\AdminPresupuestosPage( $presupuestoRepo ) )->register();
        ( new \RDT\Corralon\Admin\AdminPresupuestosController( $presupuestoRepo ) )->register();
    }

    public function onAdminMenu(): void {}

    public function onRestApiInit(): void
    {
        $presupuestoRepo = new \RDT\Corralon\Repositories\PresupuestoRepository();

        ( new \RDT\Corralon\Api\PresupuestoController(
            new \RDT\Corralon\Services\PresupuestoService(
                new \RDT\Corralon\Repositories\CarritoRepository(),
                new \RDT\Corralon\Mail\WpMailer(),
                (string) get_option( 'admin_email' ),
                $presupuestoRepo,
            )
        ) )->registerRoutes();

        ( new \RDT\Corralon\Api\CatalogoController(
            new \RDT\Corralon\Repositories\ProductoRepository()
        ) )->registerRoutes();
    }
}
