<?php

declare(strict_types=1);

namespace RDT\Corralon\Repositories;

use RDT\Corralon\Domain\LineaPresupuesto;
use RDT\Corralon\Domain\Presupuesto;
use RDT\Corralon\Domain\SolicitudPresupuesto;

interface PresupuestoRepositoryInterface
{
    /**
     * @param  LineaPresupuesto[] $lineas
     * @return int  ID del post creado (0 en caso de error)
     */
    public function guardar(SolicitudPresupuesto $solicitud, array $lineas): int;

    /** @return Presupuesto[] */
    public function findAll(int $pagina, int $por_pagina): array;

    public function contarTotal(): int;

    public function findById(int $id): ?Presupuesto;

    public function actualizarEstado(int $id, string $estado): void;

    public function actualizarNota(int $id, string $nota): void;
}
