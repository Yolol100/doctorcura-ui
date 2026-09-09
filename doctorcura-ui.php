<?php
/**
 * Plugin Name: DoctorCura UI
 * Description: DoctorCura storefront UI modules, product content, category grids, swatches, currency switching, sliders, pricing, and translation overrides.
 * Version: 1.2.7
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: OpenAI
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: doctorcura-ui
 * Domain Path: /languages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$dcui_this_file = __FILE__;
$dcui_this_path = plugin_dir_path( __FILE__ );
$dcui_this_url  = plugin_dir_url( __FILE__ );
$dcui_primary_instance = ! defined( 'DCUI_FILE' );

if ( ! defined( 'DCUI_FILE' ) ) {
    define( 'DCUI_FILE', $dcui_this_file );
}
if ( ! defined( 'DCUI_PATH' ) ) {
    define( 'DCUI_PATH', $dcui_this_path );
}
if ( ! defined( 'DCUI_URL' ) ) {
    define( 'DCUI_URL', $dcui_this_url );
}
if ( ! defined( 'DCUI_VERSION' ) ) {
    define( 'DCUI_VERSION', '1.2.7' );
}

if ( $dcui_primary_instance ) {
    add_action( 'plugins_loaded', static function (): void {
        load_plugin_textdomain( 'doctorcura-ui', false, dirname( plugin_basename( DCUI_FILE ) ) . '/languages' );
    } );

    register_deactivation_hook( $dcui_this_file, static function (): void {
        wp_clear_scheduled_hook( 'm3_update_currency_rate' );
    } );
}

// Several modules originated as Code Snippets. Skip a module when an older
// plugin copy or migrated snippet already owns its symbols; loading both would
// cause PHP "Cannot redeclare" / "class already in use" activation fatals.
if ( ! function_exists( 'dcui_current_language' ) && ! function_exists( 'dcui_text' ) ) {
    require_once $dcui_this_path . 'includes/locale.php';
}

// Uses an anonymous filter and is safe to load alongside an older UI copy.
require_once $dcui_this_path . 'includes/login-privacy-translations.php';

if ( ! function_exists( 'wa_shop_short_description' ) ) {
    require_once $dcui_this_path . 'includes/archive-short-description.php';
}
if ( ! class_exists( 'DoctorCura\\Product\\MedicalAccordionHandler', false ) ) {
    require_once $dcui_this_path . 'includes/medical-accordion.php';
}
if ( ! function_exists( 'dc_render_category_grid_shortcode' ) ) {
    require_once $dcui_this_path . 'includes/storefront-category-legacy.php';
}
if ( ! function_exists( 'shortcode_exists' ) || ! shortcode_exists( 'product_variation_choices' ) ) {
    require_once $dcui_this_path . 'includes/storefront-add-to-cart.php';
}
if ( ! class_exists( 'WA_Category_Grid', false ) ) {
    require_once $dcui_this_path . 'includes/storefront-category-grid.php';
}
if ( ! class_exists( 'M3_Elementor_Product_Image_Slider', false ) ) {
    require_once $dcui_this_path . 'includes/storefront-image-slider.php';
}
if ( ! class_exists( 'M3_Sale_Badge', false ) && ! function_exists( 'dc_show_lowest_variation_price' ) ) {
    require_once $dcui_this_path . 'includes/storefront-sale-price.php';
}
if ( ! function_exists( 'dcura_price_note_below_button' ) && ! function_exists( 'dcura_price_note_styles' ) ) {
    require_once $dcui_this_path . 'includes/storefront-archive-note.php';
}
if ( ! function_exists( 'wa_wc_translations' ) ) {
    require_once $dcui_this_path . 'includes/storefront-translations.php';
}

// Currency switching depends on WooCommerce and may already be active from an
// older copy/snippet. Load it only when WooCommerce is ready and the class is free.
add_action( 'plugins_loaded', static function () use ( $dcui_this_path ): void {
    if ( ! class_exists( 'WooCommerce' ) || class_exists( 'M3_Currency_Switcher', false ) ) {
        return;
    }

    require_once $dcui_this_path . 'includes/storefront-currency-switcher.php';
}, 20 );
