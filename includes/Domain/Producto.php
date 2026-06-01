<?php

declare(strict_types=1);

namespace RDT\Corralon\Domain;

class Producto
{
    /**
     * @param array<int, array{slug: string, nombre: string}> $categorias
     */
    public function __construct(
        public readonly int    $id,
        public readonly string $nombre,
        public readonly float  $precio,
        public readonly ?float $precio_oferta,
        public readonly array  $categorias,
        public readonly int    $stock,
        public readonly string $descripcion,
        public readonly string $sku,
        public readonly string $imagen_url,
    ) {}
}
