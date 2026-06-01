<?php

declare(strict_types=1);

namespace RDT\Corralon\Domain;

class Presupuesto
{
    /**
     * @param LineaPresupuesto[] $lineas
     */
    public function __construct(
        public readonly int                $id,
        public readonly string             $nombre,
        public readonly string             $email,
        public readonly string             $telefono,
        public readonly string             $mensaje,
        public readonly array              $lineas,
        public readonly string             $estado,
        public readonly string             $nota_interna,
        public readonly \DateTimeImmutable $fecha,
    ) {}
}
