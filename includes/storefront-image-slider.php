<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class M3_Elementor_Product_Image_Slider {
    private const SHORTCODE = 'product_image_slider';
    private const STYLE_HANDLE = 'm3-product-image-slider';
    private const SCRIPT_HANDLE = 'm3-product-image-slider';
    private const TEXT_DOMAIN = 'doctorcura-ui';
    private static bool $assets_enqueued = false;

    public function __construct() {
        add_shortcode(self::SHORTCODE, [$this, 'render']);
    }

    private function enqueue_assets(): void {
        if (self::$assets_enqueued) {
            return;
        }
        wp_enqueue_style(self::STYLE_HANDLE, DCUI_URL . 'assets/css/product-image-slider.css', [], DCUI_VERSION);
        wp_enqueue_script(self::SCRIPT_HANDLE, DCUI_URL . 'assets/js/product-image-slider.js', [], DCUI_VERSION, true);
        self::$assets_enqueued = true;
    }

    public function render(): string {
        if (!function_exists('wc_get_product')) {
            return '';
        }
        $product = wc_get_product((int) get_queried_object_id());
        if (!$product) {
            return '';
        }
        $this->enqueue_assets();
        $image_id = $product->get_image_id();
        $gallery_ids = $product->get_gallery_image_ids();
        $all_ids = array_filter(array_unique(array_merge($image_id ? [$image_id] : [], $gallery_ids)));
        if (empty($all_ids)) {
            return '';
        }
        $has_multiple = count($all_ids) > 1;
        ob_start(); ?>
        <div class="m3-product-slider <?php echo esc_attr($has_multiple ? '' : 'm3-single'); ?>" tabindex="0">
            <div class="m3-track">
                <?php foreach ($all_ids as $index => $id) : ?>
                    <div class="m3-slide">
                        <?php
                        echo wp_get_attachment_image(
                            $id,
                            'woocommerce_single',
                            false,
                            [
                                'loading' => $index === 0 ? 'eager' : 'lazy',
                                'alt' => esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true) ?: $product->get_name()),
                            ]
                        );
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($has_multiple) : ?>
                <button type="button" class="m3-arrow m3-prev" aria-label="<?php esc_attr_e('Vorheriges Bild', self::TEXT_DOMAIN); ?>">‹</button>
                <button type="button" class="m3-arrow m3-next" aria-label="<?php esc_attr_e('Nächstes Bild', self::TEXT_DOMAIN); ?>">›</button>
                <div class="m3-dots" role="tablist" aria-label="<?php esc_attr_e('Bildnavigation', self::TEXT_DOMAIN); ?>">
                    <?php foreach ($all_ids as $i => $_) : ?>
                        <button type="button" class="m3-dot <?php echo esc_attr($i === 0 ? 'is-active' : ''); ?>" role="tab" aria-selected="<?php echo esc_attr($i === 0 ? 'true' : 'false'); ?>" aria-label="<?php echo esc_attr(sprintf(__('Bild %d', self::TEXT_DOMAIN), $i + 1)); ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}

new M3_Elementor_Product_Image_Slider();
