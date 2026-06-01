<?php
/**
 * Plantilla de detalle de producto — F02
 * Sobreescribe single-product.php del tema activo via woocommerce_locate_template.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="rdt-detalle-wrap">
<?php
while (have_posts()) :
    the_post();

    global $product;
    if (!$product instanceof \WC_Product) {
        $product = wc_get_product(get_the_ID());
    }

    if (!$product instanceof \WC_Product) :
?>
    <p><?php esc_html_e('Producto no encontrado.', 'corralon-materiales'); ?></p>
<?php
    else :
        $image_id  = $product->get_image_id();
        $image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
        $nombre    = $product->get_name();
        $desc      = $product->get_description() ?: $product->get_short_description();

        $categorias = [];
        foreach ($product->get_category_ids() as $cat_id) {
            $term = get_term($cat_id, 'product_cat');
            if ($term instanceof \WP_Term) {
                $categorias[] = $term;
            }
        }

        $primera_categoria_slug = !empty($categorias) ? $categorias[0]->slug : '';
?>
    <article
        class="rdt-detalle"
        data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>"
        data-categoria="<?php echo esc_attr($primera_categoria_slug); ?>"
    >
        <div class="rdt-detalle__columnas">

            <!-- Columna izquierda: imagen (40%) -->
            <div class="rdt-detalle__imagen">
                <?php if ($image_url) : ?>
                    <img
                        src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($nombre); ?>"
                        class="rdt-detalle__img"
                    >
                <?php else : ?>
                    <div class="rdt-detalle__img-placeholder"></div>
                <?php endif; ?>
            </div>

            <!-- Columna derecha: info (60%) -->
            <div class="rdt-detalle__info">
                <h1 class="rdt-detalle__nombre"><?php echo esc_html($nombre); ?></h1>

                <?php if ($categorias) : ?>
                <div class="rdt-detalle__badges">
                    <?php foreach ($categorias as $cat) : ?>
                        <span class="catalogo-card__badge"><?php echo esc_html($cat->name); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($desc) : ?>
                <div class="rdt-detalle__descripcion">
                    <?php echo wp_kses_post($desc); ?>
                </div>
                <?php endif; ?>

                <!-- FASE 2: precio -->

                <button
                    type="button"
                    class="button rdt-detalle__btn-presupuesto"
                    data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>"
                >
                    <?php esc_html_e('Solicitar presupuesto', 'corralon-materiales'); ?>
                </button>
            </div>

        </div><!-- /.rdt-detalle__columnas -->

        <!-- Productos relacionados -->
        <div class="rdt-relacionados">
            <h2 class="rdt-relacionados__titulo">
                <?php esc_html_e('Productos relacionados', 'corralon-materiales'); ?>
            </h2>
            <div class="rdt-relacionados__grilla" id="rdt-relacionados-grilla"></div>
        </div>

    </article>

<?php
    endif;
endwhile;
?>
</div><!-- /.rdt-detalle-wrap -->

<?php get_footer(); ?>
