<?php

declare(strict_types=1);

namespace RDT\Corralon\Ui;

class HomeHooks
{
    public function register(): void
    {
        add_shortcode('home_corralon', [$this, 'renderShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('body_class', [$this, 'addBodyClass']);
    }

    /** @param string[] $classes */
    public function addBodyClass(array $classes): array
    {
        global $post;
        $has_shortcode = is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'home_corralon');
        if (is_front_page() || $has_shortcode) {
            $classes[] = 'rdt-homepage';
        }
        return $classes;
    }

    public function renderShortcode(): string
    {
        return ( new \RDT\Corralon\Shortcodes\HomeShortcode() )->render();
    }

    public function enqueueAssets(): void
    {
        global $post;

        $en_home = is_front_page()
            || is_a($post, 'WP_Post')
            && has_shortcode($post->post_content, 'home_corralon');

        if (!$en_home) {
            return;
        }

        wp_enqueue_style(
            'rdt-barlow',
            'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'rdt-home',
            RDT_CORRALON_URL . 'assets/css/home.css',
            ['rdt-barlow'],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/css/home.css' )
        );

        wp_enqueue_script(
            'rdt-home',
            RDT_CORRALON_URL . 'assets/js/home.js',
            [],
            (string) filemtime( RDT_CORRALON_PATH . 'assets/js/home.js' ),
            true
        );

        wp_localize_script('rdt-home', 'rdtHome', [
            'catalog_url'          => home_url('/catalogo/'),
            'nonce'                => wp_create_nonce('wp_rest'),
            'rest_url_suscripcion' => rest_url('corralon/v1/suscripcion'),
        ]);
    }
}
