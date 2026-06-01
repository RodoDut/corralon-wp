<?php

declare(strict_types=1);

namespace RDT\Corralon\Api;

use RDT\Corralon\Repositories\ProductoRepositoryInterface;
use WP_REST_Request;
use WP_REST_Response;

class CatalogoController
{
    private const API_NAMESPACE = 'corralon/v1';
    private const ROUTE         = '/catalogo';

    public function __construct(
        private readonly ProductoRepositoryInterface $repository,
    ) {}

    public function registerRoutes(): void
    {
        register_rest_route(self::API_NAMESPACE, self::ROUTE, [
            'methods'             => 'GET',
            'callback'            => [$this, 'handle'],
            'permission_callback' => '__return_true',
            'args'                => [
                'categoria'  => ['type' => 'string',  'default' => ''],
                'pagina'     => ['type' => 'integer', 'default' => 1,  'minimum' => 1],
                'por_pagina' => ['type' => 'integer', 'default' => 12, 'minimum' => 1, 'maximum' => 48],
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $categoria  = sanitize_text_field((string) $request->get_param('categoria'));
        $pagina     = (int) $request->get_param('pagina');
        $por_pagina = (int) $request->get_param('por_pagina');

        $productos   = $this->repository->findPaginado($pagina, $por_pagina, $categoria);
        $total       = $this->repository->contarTotal($categoria);
        $paginas     = $total > 0 ? (int) ceil($total / $por_pagina) : 0;

        return new WP_REST_Response([
            'productos'    => array_map([$this, 'serializeProducto'], $productos),
            'total'        => $total,
            'paginas'      => $paginas,
            'pagina_actual' => $pagina,
        ], 200);
    }

    private function serializeProducto(\RDT\Corralon\Domain\Producto $p): array
    {
        return [
            'id'            => $p->id,
            'nombre'        => $p->nombre,
            'categorias'    => $p->categorias,
            'imagen_url'    => $p->imagen_url,
            'precio'        => $p->precio,
            'precio_oferta' => $p->precio_oferta,
            'permalink'     => get_permalink($p->id),
        ];
    }
}
