<?php

declare(strict_types=1);

namespace RDT\Corralon\Repositories;

use RDT\Corralon\Domain\LineaPresupuesto;

class CarritoRepository implements CarritoRepositoryInterface
{
    /** @return LineaPresupuesto[] */
    public function getLineas(): array
    {
        if (!function_exists('WC') || null === WC()->cart) {
            return [];
        }

        $lineas = [];

        foreach (WC()->cart->get_cart() as $item) {
            if (!isset($item['data'], $item['quantity'])) {
                continue;
            }

            /** @var \WC_Product $product */
            $product  = $item['data'];
            $cantidad = (int) $item['quantity'];
            $precio   = (float) $product->get_price();

            $lineas[] = new LineaPresupuesto(
                nombre:         $product->get_name(),
                cantidad:       $cantidad,
                precioUnitario: $precio,
                subtotal:       $precio * $cantidad,
            );
        }

        return $lineas;
    }
}
