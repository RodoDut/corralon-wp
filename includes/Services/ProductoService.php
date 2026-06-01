<?php

declare(strict_types=1);

namespace RDT\Corralon\Services;

use RDT\Corralon\Domain\Producto;
use RDT\Corralon\Repositories\ProductoRepositoryInterface;

class ProductoService
{
    public function __construct(
        private readonly ProductoRepositoryInterface $repository,
    ) {}

    /** @return Producto[] */
    public function listarTodos(): array
    {
        return $this->repository->findAll();
    }
}
