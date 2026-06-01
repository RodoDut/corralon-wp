<?php

declare(strict_types=1);

namespace RDT\Corralon\CPT;

class PresupuestoCpt
{
    public static function register(): void
    {
        register_post_type('presupuesto', [
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'supports'      => ['title'],
            'labels'        => [
                'name'          => 'Presupuestos',
                'singular_name' => 'Presupuesto',
            ],
        ]);
    }
}
