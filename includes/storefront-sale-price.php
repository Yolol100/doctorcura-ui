<?php

/**
 * Sale
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class M3_Sale_Badge {

    private const TEXT_DOMAIN = 'doctorcura-ui';

    /**
     * Initialiseert de hooks
     */
    public static function init(): void {
        add_filter('woocommerce_sale_flash', [__CLASS__, 'render_percentage_badge'], 20, 3);
    }

    /**
     * Vervangt de standaard sale badge door een percentage badge
     *
     * @param string     $html
     * @param WP_Post    $post
     * @param WC_Product $product
     */
    public static function render_percentage_badge(string $html, $post, $product): string {

        if (!$product instanceof WC_Product || !$product->is_on_sale()) {
            return $html;
        }

        $max_percentage = self::calculate_max_percentage($product);

        if ($max_percentage <= 0) {
            return $html;
        }

        return sprintf(
            '<span class="onsale">%s</span>',
            esc_html(
                sprintf(
                    __('-%d%%', self::TEXT_DOMAIN),
                    $max_percentage
                )
            )
        );
    }

    /**
     * Berekent het hoogste kortingspercentage
     */
    private static function calculate_max_percentage(WC_Product $product): int {
        $max_percentage = 0;

        if ($product->is_type('simple')) {
            $regular = (float) $product->get_regular_price();
            $sale    = (float) $product->get_sale_price();

            if ($regular > 0 && $sale > 0 && $sale < $regular) {
                $max_percentage = (int) round((($regular - $sale) / $regular) * 100);
            }
        } elseif ($product->is_type('variable')) {
            $prices = $product->get_variation_prices();

            foreach ($prices['regular_price'] as $id => $regular_price) {
                $regular_price = (float) $regular_price;
                $sale_price    = (float) ($prices['sale_price'][$id] ?? 0);

                if ($sale_price > 0 && $sale_price < $regular_price) {
                    $percentage = (int) round((($regular_price - $sale_price) / $regular_price) * 100);
                    $max_percentage = max($max_percentage, $percentage);
                }
            }
        }

        return $max_percentage;
    }
}

// Initialiseer
M3_Sale_Badge::init();

/**
 * Laagste prijs
 */
/**
 * Toon alleen de laagste variatieprijs voor variable products
 * (zonder "Vanaf"), geschikt voor WooCommerce + Elementor.
 */
add_filter('woocommerce_variable_price_html', 'dc_show_lowest_variation_price', 10, 2);
add_filter('woocommerce_variable_sale_price_html', 'dc_show_lowest_variation_price', 10, 2);

function dc_show_lowest_variation_price($price, $product) {
    if ( ! $product instanceof WC_Product ) {
        return $price;
    }

    $min_price = $product->get_variation_price('min', true);
    $max_price = $product->get_variation_price('max', true);

    // Geen geldige prijs → fallback
    if ( $min_price === '' || $min_price === null ) {
        return $price;
    }

    // Als alle variaties dezelfde prijs hebben
    if ( (float) $min_price === (float) $max_price ) {
        return wc_price($min_price);
    }

    // Toon uitsluitend de laagste prijs
    return wc_price($min_price);
}
