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
    }

    public function onInit(): void
    {
        \RDT\Corralon\CPT\PresupuestoCpt::register();

        $productoRepo    = new \RDT\Corralon\Repositories\ProductoRepository();
        $presupuestoRepo = new \RDT\Corralon\Repositories\PresupuestoRepository();

        ( new \RDT\Corralon\Ui\HomeHooks() )->register();
        ( new \RDT\Corralon\Ui\CarritoPresupuestoHooks() )->register();
        ( new \RDT\Corralon\Ui\CatalogoHooks( $productoRepo ) )->register();
        ( new \RDT\Corralon\Ui\ProductoDetalleHooks( $productoRepo ) )->register();
        ( new \RDT\Corralon\Admin\AdminPresupuestosPage( $presupuestoRepo ) )->register();
        ( new \RDT\Corralon\Admin\AdminPresupuestosController( $presupuestoRepo ) )->register();

        // Redirige los botones nativos de WooCommerce al catálogo custom
        ( new \RDT\Corralon\Ui\WoocommerceHooks(
            home_url('/catalogo/')
        ) )->register();
    }

    public function onAdminMenu(): void {}

    public function onRestApiInit(): void
    {
        $presupuestoRepo = new \RDT\Corralon\Repositories\PresupuestoRepository();

        ( new \RDT\Corralon\Api\PresupuestoController(
            new \RDT\Corralon\Services\PresupuestoService(
                new \RDT\Corralon\Mail\WpMailer(),
                (string) get_option( 'admin_email' ),
                $presupuestoRepo,
            )
        ) )->registerRoutes();

        ( new \RDT\Corralon\Api\CatalogoController(
            new \RDT\Corralon\Repositories\ProductoRepository()
        ) )->registerRoutes();

        ( new \RDT\Corralon\Api\SuscripcionController(
            new \RDT\Corralon\Mail\SuscripcionMailer()
        ) )->registerRoutes();
    }
}
