<?php

declare(strict_types=1);

namespace RDT\Corralon\Repositories;

interface ProductoRepositoryInterface
{
    /** @return \RDT\Corralon\Domain\Producto[] */
    public function findAll(): array;

    /**
     * @return \RDT\Corralon\Domain\Producto[]
     */
    public function findPaginado(int $pagina, int $por_pagina, string $categoria = '', string $buscar = ''): array;

    public function contarTotal(string $categoria = '', string $buscar = ''): int;

    public function findById(int $id): ?\RDT\Corralon\Domain\Producto;

    /**
     * @return array<int, array{slug: string, nombre: string}>
     */
    public function getCategorias(): array;
}
