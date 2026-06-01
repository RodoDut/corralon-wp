<?php

declare(strict_types=1);

namespace RDT\Corralon\Repositories;

interface CarritoRepositoryInterface
{
    /** @return \RDT\Corralon\Domain\LineaPresupuesto[] */
    public function getLineas(): array;
}
