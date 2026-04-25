<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function (): void {
    if (!is_product()) {
        return;
    }

    wp_enqueue_style(
        'dcui-storefront-add-to-cart',
        DCUI_URL . 'assets/css/storefront-add-to-cart.css',
        [],
        DCUI_VERSION
    );

    wp_enqueue_script(
        'dcui-storefront-add-to-cart',
        DCUI_URL . 'assets/js/storefront-add-to-cart.js',
        ['jquery', 'wc-add-to-cart-variation'],
        DCUI_VERSION,
        true
    );

    wp_localize_script(
        'dcui-storefront-add-to-cart',
        'DCUIAddToCart',
        [
            'saveTextTemplate' => __('Sie sparen %s%', 'doctorcura-ui'),
        ]
    );
});

add_action('wp', function (): void {
    if (is_product()) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }
});

add_shortcode('product_variation_choices', function ($atts): string {
    if (!is_product()) {
        return '';
    }

    global $product;
    if (!$product || !$product->is_type('variable')) {
        return '';
    }

    $a = shortcode_atts(['context' => 'main'], $atts);
    $priceClass = ($a['context'] === 'sticky') ? 'gs-price-sticky' : 'gs-price-main';

    ob_start();
    ?>
    <div class="custom-variation-choices-wrapper master-variation-container">
        <?php
        wc_get_template('single-product/add-to-cart/variable.php', [
            'available_variations' => $product->get_available_variations(),
            'attributes' => $product->get_variation_attributes(),
            'selected_attributes' => $product->get_default_attributes(),
            'product' => $product,
        ]);
        ?>
        <?php if ($a['context'] === 'main') : ?>
            <div class="<?php echo esc_attr($priceClass); ?>">
                <span class="price-label-google"><?php echo esc_html__('Preis', 'doctorcura-ui'); ?></span>
                <div class="price-row-wrapper"></div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
});

add_shortcode('product_add_to_cart_button', function ($atts): string {
    if (!is_product()) {
        return '';
    }

    $a = shortcode_atts(['context' => 'main'], $atts);

    ob_start();
    ?>
    <div class="custom-add-to-cart-button-only-wrapper slave-button-container">
        <?php if ($a['context'] === 'sticky') : ?>
            <div class="gs-price-sticky">
                <span class="price-label-google"><?php echo esc_html__('Preis', 'doctorcura-ui'); ?></span>
                <div class="price-row-wrapper"></div>
            </div>
        <?php endif; ?>

        <?php woocommerce_template_single_add_to_cart(); ?>

        <?php if ($a['context'] === 'main') : ?>
            <div class="google-support-text">
                <?php echo esc_html__('Alle angezeigten Preise beinhalten ein eConsult, ein ärztliches Rezept und die 24/7 klinische Unterstützung.', 'doctorcura-ui'); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
});

add_action('wp_footer', function (): void {
    if (!is_product()) {
        return;
    }
    ?>
    <div class="google-sticky-bar" id="googleStickyBar" aria-hidden="true">
        <div class="google-sticky-bar-inner">
            <?php echo do_shortcode('[product_add_to_cart_button context="sticky"]'); ?>
        </div>
    </div>
    <?php
}, 5);

add_filter('woocommerce_show_variation_price', '__return_true');

add_filter('single_product_archive_thumbnail_size', function ($size) {
    return 'woocommerce_thumbnail';
}, 20);

add_action('after_setup_theme', function (): void {
    if (!class_exists('WooCommerce')) {
        return;
    }

    remove_theme_support('wc-product-gallery-zoom');
}, 100);
