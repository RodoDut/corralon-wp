<?php

declare(strict_types=1);

namespace RDT\Corralon\Repositories;

use RDT\Corralon\Domain\LineaPresupuesto;
use RDT\Corralon\Domain\Presupuesto;
use RDT\Corralon\Domain\PresupuestoEstado;
use RDT\Corralon\Domain\SolicitudPresupuesto;

class PresupuestoRepository implements PresupuestoRepositoryInterface
{
    public function guardar(SolicitudPresupuesto $solicitud, array $lineas): int
    {
        $post_id = wp_insert_post([
            'post_type'   => 'presupuesto',
            'post_status' => 'publish',
            'post_title'  => 'Presupuesto — ' . $solicitud->nombre,
        ]);

        if (is_wp_error($post_id) || $post_id === 0) {
            return 0;
        }

        update_post_meta($post_id, '_nombre',       $solicitud->nombre);
        update_post_meta($post_id, '_email',        $solicitud->email);
        update_post_meta($post_id, '_telefono',     $solicitud->telefono);
        update_post_meta($post_id, '_mensaje',      $solicitud->mensaje);
        update_post_meta($post_id, '_estado',       PresupuestoEstado::PENDIENTE);
        update_post_meta($post_id, '_nota_interna', '');
        update_post_meta($post_id, '_lineas',       wp_json_encode(
            array_map(fn(LineaPresupuesto $l) => [
                'nombre'         => $l->nombre,
                'cantidad'       => $l->cantidad,
                'precioUnitario' => $l->precioUnitario,
                'subtotal'       => $l->subtotal,
            ], $lineas)
        ));

        wp_update_post([
            'ID'         => $post_id,
            'post_title' => sprintf('Presupuesto #%d — %s', $post_id, $solicitud->nombre),
        ]);

        return $post_id;
    }

    /** @return Presupuesto[] */
    public function findAll(int $pagina, int $por_pagina): array
    {
        $query = new \WP_Query([
            'post_type'      => 'presupuesto',
            'post_status'    => 'publish',
            'posts_per_page' => $por_pagina,
            'paged'          => $pagina,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        return array_map([$this, 'buildPresupuesto'], $query->posts);
    }

    public function contarTotal(): int
    {
        $query = new \WP_Query([
            'post_type'      => 'presupuesto',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        return count($query->posts);
    }

    public function findById(int $id): ?Presupuesto
    {
        $post = get_post($id);

        if (!$post instanceof \WP_Post || $post->post_type !== 'presupuesto') {
            return null;
        }

        return $this->buildPresupuesto($post);
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        update_post_meta($id, '_estado', $estado);
    }

    public function actualizarNota(int $id, string $nota): void
    {
        update_post_meta($id, '_nota_interna', $nota);
    }

    private function buildPresupuesto(\WP_Post $post): Presupuesto
    {
        $lineasRaw = json_decode((string) get_post_meta($post->ID, '_lineas', true), true);
        $lineas    = [];

        if (is_array($lineasRaw)) {
            foreach ($lineasRaw as $l) {
                $lineas[] = new LineaPresupuesto(
                    nombre:         (string) ($l['nombre']         ?? ''),
                    cantidad:       (int)    ($l['cantidad']       ?? 0),
                    precioUnitario: (float)  ($l['precioUnitario'] ?? 0.0),
                    subtotal:       (float)  ($l['subtotal']       ?? 0.0),
                );
            }
        }

        $estado = (string) get_post_meta($post->ID, '_estado', true);

        return new Presupuesto(
            id:           $post->ID,
            nombre:       (string) get_post_meta($post->ID, '_nombre',       true),
            email:        (string) get_post_meta($post->ID, '_email',        true),
            telefono:     (string) get_post_meta($post->ID, '_telefono',     true),
            mensaje:      (string) get_post_meta($post->ID, '_mensaje',      true),
            lineas:       $lineas,
            estado:       $estado !== '' ? $estado : PresupuestoEstado::PENDIENTE,
            nota_interna: (string) get_post_meta($post->ID, '_nota_interna', true),
            fecha:        new \DateTimeImmutable($post->post_date),
        );
    }
}
