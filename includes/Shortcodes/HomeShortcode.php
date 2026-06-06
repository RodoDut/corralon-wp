<?php

declare(strict_types=1);

namespace RDT\Corralon\Shortcodes;

class HomeShortcode
{
    public function render(): string
    {
        $catalog_url = home_url('/catalogo/');

        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'orderby'    => 'name',
        ]);

        $default_cat_id = (int) get_option('default_product_cat', 0);

        /** @var \WP_Term[] $categorias */
        $categorias = [];
        if (\is_array($terms)) {
            foreach ($terms as $term) {
                if (
                    $term instanceof \WP_Term
                    && $term->slug !== 'uncategorized'
                    && $term->term_id !== $default_cat_id
                ) {
                    $categorias[] = $term;
                }
            }
        }

        ob_start();
        ?>
<div class="rdt-home">

    <!-- ========== BARRA DE CATEGORÍAS ========== -->
    <nav class="rdt-home__cat-nav" aria-label="<?php esc_attr_e('Categorías', 'corralon-materiales'); ?>">
        <button class="rdt-home__cat-nav-item active" type="button" data-cat="all">
            <?php esc_html_e('Todos', 'corralon-materiales'); ?>
        </button>
        <?php foreach ($categorias as $cat) : ?>
            <button
                class="rdt-home__cat-nav-item"
                type="button"
                data-cat="<?php echo esc_attr($cat->slug); ?>"
            ><?php echo esc_html($cat->name); ?></button>
        <?php endforeach; ?>
    </nav>

    <!-- ========== HERO ========== -->
    <section class="rdt-home__hero" aria-labelledby="rdt-hero-title">
        <div class="rdt-home__hero-content">
            <h1 class="rdt-home__hero-title" id="rdt-hero-title">
                <?php esc_html_e('Todo para tu obra,', 'corralon-materiales'); ?>
                <span><?php esc_html_e('en un solo lugar', 'corralon-materiales'); ?></span>
            </h1>
            <p class="rdt-home__hero-sub">
                <?php esc_html_e('Consultá disponibilidad y solicitá tu presupuesto al instante', 'corralon-materiales'); ?>
            </p>
            <a href="<?php echo esc_url($catalog_url); ?>" class="rdt-home__btn-hero">
                <?php esc_html_e('Ver catálogo', 'corralon-materiales'); ?>
            </a>
        </div>
    </section>

    <!-- ========== CATEGORÍAS DESTACADAS ========== -->
    <?php if (!empty($categorias)) : ?>
    <section class="rdt-home__categorias">
        <div class="rdt-home__section-header">
            <h2 class="rdt-home__section-title">
                <?php esc_html_e('Categorías', 'corralon-materiales'); ?>
            </h2>
            <a href="<?php echo esc_url($catalog_url); ?>" class="rdt-home__ver-todo">
                <?php esc_html_e('Ver todo', 'corralon-materiales'); ?>
            </a>
        </div>
        <div class="rdt-home__cat-grid">
            <?php foreach ($categorias as $cat) :
                $thumb_id  = (int) get_term_meta($cat->term_id, 'thumbnail_id', true);
                $thumb_url = $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'medium') : '';
            ?>
                <a
                    href="<?php echo esc_url($catalog_url); ?>"
                    class="rdt-home__cat-card"
                    data-cat="<?php echo esc_attr($cat->slug); ?>"
                >
                    <div class="rdt-home__cat-card-img">
                        <?php if ($thumb_url) : ?>
                            <img
                                src="<?php echo esc_url($thumb_url); ?>"
                                alt="<?php echo esc_attr($cat->name); ?>"
                                loading="lazy"
                            >
                        <?php else : ?>
                            <div class="rdt-home__cat-card-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <span class="rdt-home__cat-card-name">
                        <?php echo esc_html($cat->name); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== BANNER SUSCRIPCIÓN ========== -->
    <section class="rdt-home__banner" id="rdt-home-suscripcion">
        <div class="rdt-home__banner-texto">
            <h3><?php esc_html_e('No te pierdas nuestras ofertas', 'corralon-materiales'); ?></h3>
            <p><?php esc_html_e('Recibí precios especiales y novedades directo en tu email', 'corralon-materiales'); ?></p>
        </div>
        <form class="rdt-home__suscripcion-form" novalidate>
            <input
                type="email"
                name="email"
                placeholder="<?php esc_attr_e('tu@email.com', 'corralon-materiales'); ?>"
                required
                aria-label="<?php esc_attr_e('Tu email', 'corralon-materiales'); ?>"
            >
            <button type="submit">
                <?php esc_html_e('Suscribirme', 'corralon-materiales'); ?>
            </button>
        </form>
    </section>

</div><!-- /.rdt-home -->
        <?php
        return (string) ob_get_clean();
    }
}
