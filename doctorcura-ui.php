<?php
/**
 * Plugin Name: DoctorCura UI
 * Description: DoctorCura storefront UI modules, product content, category grids, swatches, currency switching, sliders, pricing, and translation overrides.
 * Version: 1.2.5
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: OpenAI
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: doctorcura-ui
 * Domain Path: /languages
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('DCUI_FILE', __FILE__);
define('DCUI_PATH', plugin_dir_path(__FILE__));
define('DCUI_URL', plugin_dir_url(__FILE__));
define('DCUI_VERSION', '1.2.5');

add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('doctorcura-ui', false, dirname(plugin_basename(DCUI_FILE)) . '/languages');
});

register_deactivation_hook(DCUI_FILE, static function (): void {
    wp_clear_scheduled_hook('m3_update_currency_rate');
});

require_once DCUI_PATH . 'includes/locale.php';
require_once DCUI_PATH . 'includes/archive-short-description.php';
require_once DCUI_PATH . 'includes/medical-accordion.php';
require_once DCUI_PATH . 'includes/storefront-category-legacy.php';
require_once DCUI_PATH . 'includes/storefront-add-to-cart.php';
require_once DCUI_PATH . 'includes/storefront-category-grid.php';
require_once DCUI_PATH . 'includes/storefront-image-slider.php';
require_once DCUI_PATH . 'includes/storefront-sale-price.php';
require_once DCUI_PATH . 'includes/storefront-archive-note.php';
require_once DCUI_PATH . 'includes/storefront-translations.php';

// Currency switching depends on WooCommerce. Load it only after all plugins are available,
// otherwise the module can return before registering the [currency_switcher] shortcode.
add_action('plugins_loaded', static function (): void {
    if (!class_exists('WooCommerce')) {
        return;
    }

    require_once DCUI_PATH . 'includes/storefront-currency-switcher.php';
}, 20);
