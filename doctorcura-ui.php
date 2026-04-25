<?php
/**
 * Plugin Name: DoctorCura UI
 * Description: DoctorCura storefront UI modules, product content, category grids, swatches, currency switching, sliders, pricing, and translation overrides.
 * Version: 1.2.3
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: OpenAI
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
define('DCUI_VERSION', '1.2.3');

add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('doctorcura-ui', false, dirname(plugin_basename(DCUI_FILE)) . '/languages');
});

require_once DCUI_PATH . 'includes/archive-short-description.php';
require_once DCUI_PATH . 'includes/medical-accordion.php';
require_once DCUI_PATH . 'includes/storefront-category-legacy.php';
require_once DCUI_PATH . 'includes/storefront-add-to-cart.php';
require_once DCUI_PATH . 'includes/storefront-category-grid.php';
require_once DCUI_PATH . 'includes/storefront-currency-switcher.php';
require_once DCUI_PATH . 'includes/storefront-image-slider.php';
require_once DCUI_PATH . 'includes/storefront-sale-price.php';
require_once DCUI_PATH . 'includes/storefront-archive-note.php';
require_once DCUI_PATH . 'includes/storefront-translations.php';
