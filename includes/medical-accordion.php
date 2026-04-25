<?php

declare(strict_types=1);

namespace DoctorCura\Product;

use WC_Product;
use WP_Post;

defined('ABSPATH') || exit;

final class MedicalAccordionHandler
{
    private const META_FIELDS = [
        '_how_it_works' => 'Wirkungsweise',
        '_medical_info' => 'Medizinische Informationen',
        '_product_faq'  => 'Häufig gestellte Fragen',
    ];

    public static function init(): void
    {
        new self();
    }

    private function __construct()
    {
        add_filter('woocommerce_product_data_tabs', [$this, 'add_medical_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'add_medical_panel']);
        add_action('woocommerce_admin_process_product_object', [$this, 'save_medical_data']);
        add_shortcode('medical_accordion', [$this, 'render_accordion']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_medical_tab(array $tabs): array
    {
        $tabs['medical_content'] = [
            'label'    => __('Medizinische Inhalte', 'doctorcura-ui'),
            'target'   => 'medical_content_data',
            'class'    => ['show_if_simple', 'show_if_variable'],
            'priority' => 60,
        ];

        return $tabs;
    }

    public function add_medical_panel(): void
    {
        global $post;

        if (!$post instanceof WP_Post) {
            return;
        }
        ?>
        <div id="medical_content_data" class="panel woocommerce_options_panel">
            <?php wp_nonce_field('wa_medical_save', 'wa_medical_nonce'); ?>

            <?php foreach (self::META_FIELDS as $key => $label) :
                $title_key = $key . '_title';
                $content   = get_post_meta($post->ID, $key, true);
                $title_val = get_post_meta($post->ID, $title_key, true);
                $translated_label = __($label, 'doctorcura-ui');
                ?>
                <div class="options_group" style="padding:15px;border-bottom:1px solid #eee;">
                    <?php
                    woocommerce_wp_text_input([
                        'id'          => $title_key,
                        'label'       => sprintf(__('Titel überschreiben: %s', 'doctorcura-ui'), $translated_label),
                        'value'       => $title_val,
                        'description' => __('Leer lassen für den Standardtitel', 'doctorcura-ui'),
                        'desc_tip'    => true,
                    ]);
                    ?>
                    <div style="margin:10px 161px;">
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">
                            <?php echo esc_html($translated_label); ?>
                        </label>
                        <?php
                        wp_editor(
                            $content,
                            sanitize_key('wa_edit_' . $key),
                            [
                                'textarea_name' => $key,
                                'textarea_rows' => 6,
                                'media_buttons' => false,
                                'teeny'         => true,
                            ]
                        );
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function save_medical_data(WC_Product $product): void
    {
        if (!isset($_POST['wa_medical_nonce']) || !wp_verify_nonce(wp_unslash($_POST['wa_medical_nonce']), 'wa_medical_save')) {
            return;
        }

        foreach (self::META_FIELDS as $key => $_label) {
            if (isset($_POST[$key])) {
                $product->update_meta_data($key, wp_kses_post(wp_unslash($_POST[$key])));
            }

            $title_key = $key . '_title';
            if (isset($_POST[$title_key])) {
                $product->update_meta_data($title_key, sanitize_text_field(wp_unslash($_POST[$title_key])));
            }
        }
    }

    public function enqueue_assets(): void
    {
        if (!is_product()) {
            return;
        }

        wp_enqueue_style(
            'dcui-medical-accordion',
            DCUI_URL . 'assets/css/medical-accordion.css',
            [],
            DCUI_VERSION
        );

        wp_enqueue_script(
            'dcui-medical-accordion',
            DCUI_URL . 'assets/js/medical-accordion.js',
            ['jquery'],
            DCUI_VERSION,
            true
        );
    }

    public function render_accordion(): string
    {
        if (!is_product()) {
            return '';
        }

        $product = wc_get_product(get_the_ID());
        if (!$product instanceof WC_Product) {
            return '';
        }

        $items = [];

        ob_start();
        wc_display_product_attributes($product);
        $attributes = trim((string) ob_get_clean());

        if ($attributes !== '') {
            $items[] = [
                'id'      => 'specs',
                'title'   => __('Spezifikationen', 'doctorcura-ui'),
                'content' => $attributes,
            ];
        }

        foreach (self::META_FIELDS as $key => $default_label) {
            $content = $product->get_meta($key);
            if (!$content) {
                continue;
            }

            $custom_title = $product->get_meta($key . '_title');
            $items[] = [
                'id'      => sanitize_title($key),
                'title'   => $custom_title ?: __($default_label, 'doctorcura-ui'),
                'content' => wc_format_content($content),
            ];
        }

        if (!$items && !$product->get_short_description()) {
            return '';
        }

        ob_start(); ?>
        <div class="wa-product-content-wrapper">
            <?php if ($product->get_short_description()) : ?>
                <div class="wa-description-section">
                    <h3 class="wa-description-title"><?php echo esc_html__('Beschreibung', 'doctorcura-ui'); ?></h3>
                    <div class="wa-description-body">
                        <?php echo wp_kses_post(apply_filters('woocommerce_short_description', $product->get_short_description())); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="wa-google-accordion">
                <?php foreach ($items as $item) :
                    $panel_id = 'panel-' . esc_attr($item['id']); ?>
                    <div class="wa-acc-row" id="acc-<?php echo esc_attr($item['id']); ?>">
                        <button type="button" class="wa-acc-trigger" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>">
                            <span class="wa-acc-label"><?php echo esc_html($item['title']); ?></span>
                            <span class="wa-acc-chevron" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </button>
                        <div class="wa-acc-panel" id="<?php echo esc_attr($panel_id); ?>" hidden>
                            <div class="wa-acc-body"><?php echo wp_kses_post($item['content']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}

MedicalAccordionHandler::init();
