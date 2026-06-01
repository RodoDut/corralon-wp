<?php

declare(strict_types=1);

namespace RDT\Corralon\Domain;

class LineaPresupuesto
{
    public function __construct(
        public readonly string $nombre,
        public readonly int    $cantidad,
        public readonly float  $precioUnitario,
        public readonly float  $subtotal,
    ) {}
}