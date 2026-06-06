<?php

declare(strict_types=1);

namespace RDT\Corralon\Repositories;

use RDT\Corralon\Domain\Producto;

class ProductoRepository implements ProductoRepositoryInterface
{
    /** @return Producto[] */
    public function findAll(): array
    {
        if (!function_exists('wc_get_products')) {
            return [];
        }

        $query = new \WC_Product_Query([
            'limit'  => -1,
            'status' => 'publish',
        ]);

        return array_map([$this, 'buildProducto'], $query->get_products());
    }

    /** @return Producto[] */
    public function findPaginado(int $pagina, int $por_pagina, string $categoria = '', string $buscar = ''): array
    {
        if (!function_exists('wc_get_products')) {
            return [];
        }

        $args = [
            'limit'  => $por_pagina,
            'page'   => $pagina,
            'status' => 'publish',
        ];

        if ($categoria !== '') {
            $args['category'] = [$categoria];
        }

        // La búsqueda por texto no es soportada nativamente por WC_Product_Query,
        // así que la inyectamos mediante un filtro de WP_Query.
        if ($buscar !== '') {
            $buscar_sanitized = sanitize_text_field($buscar);
            $filter = static function (array $q_args) use ($buscar_sanitized): array {
                $q_args['s'] = $buscar_sanitized;
                return $q_args;
            };
            add_filter('woocommerce_product_query_meta_query', $filter);
            add_filter('woocommerce_product_data_store_cpt_get_products_query', $filter);
        }

        $query    = new \WC_Product_Query($args);
        $products = $query->get_products();

        if ($buscar !== '') {
            remove_filter('woocommerce_product_query_meta_query', $filter);
            remove_filter('woocommerce_product_data_store_cpt_get_products_query', $filter);
        }

        return array_map([$this, 'buildProducto'], $products);
    }

    public function contarTotal(string $categoria = '', string $buscar = ''): int
    {
        if (!function_exists('wc_get_products')) {
            return 0;
        }

        $args = [
            'limit'  => -1,
            'status' => 'publish',
            'return' => 'ids',
        ];

        if ($categoria !== '') {
            $args['category'] = [$categoria];
        }

        if ($buscar !== '') {
            $buscar_sanitized = sanitize_text_field($buscar);
            $filter = static function (array $q_args) use ($buscar_sanitized): array {
                $q_args['s'] = $buscar_sanitized;
                return $q_args;
            };
            add_filter('woocommerce_product_data_store_cpt_get_products_query', $filter);
        }

        $query = new \WC_Product_Query($args);
        $count = count($query->get_products());

        if ($buscar !== '') {
            remove_filter('woocommerce_product_data_store_cpt_get_products_query', $filter);
        }

        return $count;
    }

    /**
     * @return array<int, array{slug: string, nombre: string}>
     */
    public function getCategorias(): array
    {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
        ]);

        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        return array_values(array_map(
            fn(\WP_Term $t) => ['slug' => $t->slug, 'nombre' => $t->name],
            $terms
        ));
    }

    public function findById(int $id): ?Producto
    {
        if (!function_exists('wc_get_product')) {
            return null;
        }

        $wc = wc_get_product($id);

        if (!$wc instanceof \WC_Product || $wc->get_status() !== 'publish') {
            return null;
        }

        return $this->buildProducto($wc, 'full');
    }

    private function buildProducto(\WC_Product $wc, string $imageSize = 'woocommerce_thumbnail'): Producto
    {
        $imageId  = $wc->get_image_id();
        $imageUrl = $imageId
            ? (string) wp_get_attachment_image_url($imageId, $imageSize)
            : '';

        return new Producto(
            id:            $wc->get_id(),
            nombre:        $wc->get_name(),
            precio:        (float) ($wc->get_regular_price() ?: $wc->get_price()),
            precio_oferta: $wc->is_on_sale() ? (float) $wc->get_sale_price() : null,
            categorias:    $this->buildCategorias($wc->get_category_ids()),
            stock:         $wc->get_stock_quantity() ?? 0,
            descripcion:   $wc->get_short_description(),
            sku:           $wc->get_sku(),
            imagen_url:    $imageUrl,
        );
    }

    /**
     * @param int[] $ids
     * @return array<int, array{slug: string, nombre: string}>
     */
    private function buildCategorias(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $term = get_term($id, 'product_cat');
            if ($term instanceof \WP_Term) {
                $result[] = ['slug' => $term->slug, 'nombre' => $term->name];
            }
        }
        return $result;
    }
}
