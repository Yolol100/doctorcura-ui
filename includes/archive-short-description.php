<?php

/**
 * Productbeschrijving
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_after_shop_loop_item_title', 'wa_shop_short_description', 9);

function wa_shop_short_description(): void
{
    if (is_admin()) {
        return;
    }

    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }

    $description = $product->get_short_description();

    if ($description === '') {
        return;
    }
    ?>
    <div class="hfe-product-description">
        <?php
        echo wp_kses_post(
            wp_trim_words(
                apply_filters('woocommerce_short_description', $description),
                20,
                '&hellip;'
            )
        );
        ?>
    </div>
    <?php
}

