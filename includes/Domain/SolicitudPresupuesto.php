<?php

declare(strict_types=1);

namespace RDT\Corralon\Domain;

class SolicitudPresupuesto
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly string $mensaje,
    ) {}
}
