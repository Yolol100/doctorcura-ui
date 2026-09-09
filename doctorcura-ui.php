<?php
/**
 * Plugin Name: DoctorCura UI
 * Description: DoctorCura storefront UI modules, product content, category grids, swatches, currency switching, sliders, pricing, and translation overrides.
 * Version: 1.3.2
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Author: Webactueel Team
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: doctorcura-ui
 * Domain Path: /languages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$dcui_this_file     = __FILE__;
$dcui_this_path     = plugin_dir_path( __FILE__ );
$dcui_this_url      = plugin_dir_url( __FILE__ );
$dcui_this_basename = plugin_basename( __FILE__ );

// Detect duplicate active DoctorCura UI plugin files before defining any symbols.
// This also protects the load-order case where this fixed copy loads before an
// older unguarded copy: the fixed copy yields, so the later legacy include cannot
// crash the request with constant/function redeclarations.
$dcui_active_plugins = function_exists( 'get_option' ) ? (array) get_option( 'active_plugins', [] ) : [];
if ( function_exists( 'is_multisite' ) && is_multisite() && function_exists( 'get_site_option' ) ) {
    $dcui_network_plugins = (array) get_site_option( 'active_sitewide_plugins', [] );
    $dcui_active_plugins  = array_merge( $dcui_active_plugins, array_keys( $dcui_network_plugins ) );
}

$dcui_duplicate_plugins = array_values(
    array_filter(
        $dcui_active_plugins,
        static function ( $plugin ) use ( $dcui_this_basename ): bool {
            return is_string( $plugin )
                && $plugin !== $dcui_this_basename
                && 'doctorcura-ui.php' === basename( $plugin );
        }
    )
);

if ( $dcui_duplicate_plugins && ! defined( 'DCUI_FILE' ) ) {
    error_log( '[DoctorCura UI] Startup skipped because another DoctorCura UI plugin file is active: ' . implode( ', ', $dcui_duplicate_plugins ) );

    add_action( 'admin_notices', static function () use ( $dcui_this_basename, $dcui_duplicate_plugins ): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'DoctorCura UI conflict:', 'doctorcura-ui' ) . '</strong> ' . esc_html(
            sprintf(
                'Multiple DoctorCura UI plugin copies are active (%s, %s). This copy stayed inactive for this request to prevent a PHP fatal. Deactivate/remove the duplicate copy and keep only one DoctorCura UI plugin active.',
                $dcui_this_basename,
                implode( ', ', $dcui_duplicate_plugins )
            )
        ) . '</p></div>';
    } );

    return;
}

// A second DoctorCura UI copy cannot be loaded safely. Do not continue silently.
if ( defined( 'DCUI_FILE' ) && (string) DCUI_FILE !== $dcui_this_file ) {
    $existing_file = (string) DCUI_FILE;

    error_log( '[DoctorCura UI] Duplicate plugin copy blocked. Already loaded: ' . $existing_file . '; skipped: ' . $dcui_this_file );

    add_action( 'admin_notices', static function () use ( $existing_file, $dcui_this_file ): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
            esc_html__( 'DoctorCura UI conflict:', 'doctorcura-ui' ),
            esc_html( sprintf( 'Multiple plugin copies are active. Loaded: %s. Blocked: %s. Keep only one DoctorCura UI copy active.', $existing_file, $dcui_this_file ) )
        );
    } );

    return;
}

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
    define( 'DCUI_VERSION', '1.3.2' );
}

add_action( 'init', static function (): void {
    load_plugin_textdomain( 'doctorcura-ui', false, dirname( plugin_basename( DCUI_FILE ) ) . '/languages' );
}, 0 );

register_activation_hook( DCUI_FILE, static function (): void {
    if ( class_exists( 'WooCommerce' ) ) {
        return;
    }

    if ( ! function_exists( 'deactivate_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    deactivate_plugins( plugin_basename( DCUI_FILE ) );

    wp_die(
        esc_html__( 'DoctorCura UI requires WooCommerce to be active.', 'doctorcura-ui' ),
        esc_html__( 'Plugin dependency missing', 'doctorcura-ui' ),
        [ 'back_link' => true ]
    );
} );

register_deactivation_hook( DCUI_FILE, static function (): void {
    wp_clear_scheduled_hook( 'm3_update_currency_rate' );
} );

add_action( 'plugins_loaded', static function () use ( $dcui_this_path ): void {
    if ( ! class_exists( 'WooCommerce' ) ) {
        error_log( '[DoctorCura UI] WooCommerce is not active; storefront modules were not loaded.' );

        add_action( 'admin_notices', static function (): void {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'DoctorCura UI:', 'doctorcura-ui' ) . '</strong> ' . esc_html__( 'WooCommerce is required. Activate WooCommerce before using this plugin.', 'doctorcura-ui' ) . '</p></div>';
        } );

        return;
    }

    $conflicts = [];

    $symbol_exists = static function ( string $symbol ): bool {
        if ( str_starts_with( $symbol, 'function:' ) ) {
            return function_exists( substr( $symbol, 9 ) );
        }

        if ( str_starts_with( $symbol, 'class:' ) ) {
            return class_exists( substr( $symbol, 6 ), false );
        }

        if ( str_starts_with( $symbol, 'shortcode:' ) ) {
            return function_exists( 'shortcode_exists' ) && shortcode_exists( substr( $symbol, 10 ) );
        }

        return false;
    };

    $load_module = static function ( string $relative_path, array $symbols ) use ( $dcui_this_path, $symbol_exists, &$conflicts ): void {
        $found = [];

        foreach ( $symbols as $symbol ) {
            if ( $symbol_exists( $symbol ) ) {
                $found[] = $symbol;
            }
        }

        if ( $found ) {
            $conflicts[ $relative_path ] = $found;
            error_log( '[DoctorCura UI] Module skipped because legacy symbols are already loaded: ' . $relative_path . ' => ' . implode( ', ', $found ) );
            return;
        }

        require_once $dcui_this_path . $relative_path;
    };

    // Locale helpers are individually guarded inside the file, so partial legacy
    // migrations cannot leave dcui_text() or dcui_current_language() undefined.
    // Existing legacy helpers are reported, but the missing counterpart is still loaded.
    $locale_legacy_symbols = [];
    foreach ( [ 'dcui_current_language', 'dcui_text' ] as $locale_symbol ) {
        if ( function_exists( $locale_symbol ) ) {
            $locale_legacy_symbols[] = 'function:' . $locale_symbol;
        }
    }
    if ( $locale_legacy_symbols ) {
        $conflicts['includes/locale.php'] = $locale_legacy_symbols;
        error_log( '[DoctorCura UI] Legacy locale helper detected: ' . implode( ', ', $locale_legacy_symbols ) );
    }
    require_once $dcui_this_path . 'includes/locale.php';

    // Core/account behavior is isolated from storefront display code.
    require_once $dcui_this_path . 'includes/account-notifications.php';
    require_once $dcui_this_path . 'includes/login-privacy-translations.php';

    $load_module( 'includes/archive-short-description.php', [
        'function:wa_shop_short_description',
    ] );

    $load_module( 'includes/medical-accordion.php', [
        'class:DoctorCura\\Product\\MedicalAccordionHandler',
        'shortcode:medical_accordion',
    ] );

    $load_module( 'includes/storefront-category-grid.php', [
        'class:WA_Category_Grid',
        'class:DCUI_Category_Grid_Admin',
        'shortcode:wa_category_grid',
        'shortcode:alle_categorieen',
    ] );

    $load_module( 'includes/storefront-add-to-cart.php', [
        'shortcode:product_variation_choices',
        'shortcode:product_add_to_cart_button',
    ] );

    $load_module( 'includes/storefront-image-slider.php', [
        'class:M3_Elementor_Product_Image_Slider',
        'shortcode:product_image_slider',
    ] );

    $load_module( 'includes/storefront-sale-price.php', [
        'class:M3_Sale_Badge',
        'function:dc_show_lowest_variation_price',
    ] );

    $load_module( 'includes/storefront-archive-note.php', [
        'function:dcura_price_note_below_button',
        'function:dcura_price_note_styles',
    ] );

    $load_module( 'includes/storefront-translations.php', [
        'function:wa_wc_translations',
        'function:wa_wc_t',
        'function:wa_wc_translate_strings',
        'function:wa_wc_translate_nstrings',
        'function:wa_wc_fix_account_menu_labels',
        'function:wa_wc_customize_checkout_fields',
        'function:wa_wc_coupon_placeholder',
        'function:wa_wc_loop_cart_button_text',
        'function:wa_wc_single_cart_button_text',
        'function:dcura_price_note_next_to_price',
        'function:dcui_translation_assets',
    ] );

    $load_module( 'includes/storefront-currency-switcher.php', [
        'class:M3_Currency_Switcher',
        'shortcode:currency_switcher',
    ] );

    if ( $conflicts ) {
        add_action( 'admin_notices', static function () use ( $conflicts ): void {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $details = [];
            foreach ( $conflicts as $module => $symbols ) {
                $details[] = $module . ': ' . implode( ', ', $symbols );
            }

            echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'DoctorCura UI legacy-code conflict:', 'doctorcura-ui' ) . '</strong> ' . esc_html__( 'Legacy DoctorCura UI symbols were detected in an older plugin copy, Code Snippet, MU plugin, or theme. Directly conflicting modules were skipped to prevent a PHP fatal; missing locale helpers were still loaded when safe. Disable the duplicate code. Details:', 'doctorcura-ui' ) . '</p><p><code>' . esc_html( implode( ' | ', $details ) ) . '</code></p></div>';
        } );
    }
}, 20 );
