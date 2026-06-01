<?php
/**
 * Plugin Name: Corralón Materiales
 * Plugin URI:  https://rdtecno.com
 * Description: Gestión de productos para corralonería.
 * Version:     1.0.0
 * Author:      RD Tecno
 * Author URI:  https://rdtecno.com
 * License:     GPL-2.0-or-later
 * Text Domain: corralon-materiales
 * Requires PHP: 8.1
 */


declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load WordPress core functions
//require_once ABSPATH . 'wp-load.php';

define( 'RDT_CORRALON_PATH', plugin_dir_path( __FILE__ ) );
define( 'RDT_CORRALON_URL',  plugin_dir_url( __FILE__ ) );

require_once RDT_CORRALON_PATH . 'vendor/autoload.php';

( new \RDT\Corralon\Bootstrap\Plugin() )->register();
