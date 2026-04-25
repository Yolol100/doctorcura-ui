<?php

/**
 * Grid
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class WA_Category_Grid
{
    public static function init(): void
    {
        add_shortcode('wa_category_grid', [__CLASS__, 'render_grid']);
    }

    public static function render_grid($atts = []): string
    {
        $atts = shortcode_atts([
            'category_ids' => '',
            'categories'   => '',
            'hide_empty'   => 'true',
            'exclude'      => 'uncategorized,alle-behandlungen,alle-medikamente,mann,frau',
        ], $atts, 'wa_category_grid');

        $hide_empty = filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN);
        $exclude_slugs = self::parse_csv((string) $atts['exclude']);

        $terms = [];

        $category_ids = array_filter(array_map('absint', explode(',', (string) $atts['category_ids'])));
        if (!empty($category_ids)) {
            foreach ($category_ids as $term_id) {
                $term = get_term($term_id, 'product_cat');

                if ($term instanceof WP_Term && !is_wp_error($term)) {
                    $terms[] = $term;
                }
            }
        } else {
            $requested_categories = self::parse_csv((string) $atts['categories']);

            if (!empty($requested_categories)) {
                foreach ($requested_categories as $value) {
                    $term = self::find_term_by_slug_or_name($value, 'product_cat');

                    if ($term instanceof WP_Term && !is_wp_error($term)) {
                        $terms[] = $term;
                    }
                }
            } else {
                $all_terms = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => $hide_empty,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);

                if (!is_wp_error($all_terms) && is_array($all_terms)) {
                    foreach ($all_terms as $term) {
                        if ($term instanceof WP_Term) {
                            $terms[] = $term;
                        }
                    }
                }
            }
        }

        if (empty($terms)) {
            return '';
        }

        $terms = self::unique_terms($terms);

        $categories_data = [];

        foreach ($terms as $term) {
            if (!$term instanceof WP_Term) {
                continue;
            }

            if (in_array($term->slug, $exclude_slugs, true)) {
                continue;
            }

            if ($hide_empty && (int) $term->count < 1) {
                continue;
            }

            $thumbnail_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
            $image_url = $thumbnail_id > 0
                ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large')
                : wc_placeholder_img_src();

            $term_link = get_term_link($term);
            if (is_wp_error($term_link)) {
                continue;
            }

            $categories_data[] = [
                'title' => $term->name,
                'link'  => $term_link,
                'img'   => $image_url,
                'count' => (int) $term->count,
            ];
        }

        if (empty($categories_data)) {
            return '';
        }

        wp_enqueue_style('dcui-category-grid', DCUI_URL . 'assets/css/storefront-category-grid.css', [], DCUI_VERSION);

        ob_start();
        ?>
        <nav class="vca-grid-wrapper" aria-label="<?php echo esc_attr__('Produktkategorien', 'doctorcura'); ?>">
            <div class="vca-layout">
                <?php foreach ($categories_data as $cat): ?>
                    <a href="<?php echo esc_url((string) $cat['link']); ?>" class="vca-item-link">
                        <h3 class="vca-item-title"><?php echo esc_html((string) $cat['title']); ?></h3>
                        <div class="vca-image-box">
                            <img
                                src="<?php echo esc_url((string) $cat['img']); ?>"
                                class="vca-cat-img"
                                alt="<?php echo esc_attr((string) $cat['title']); ?>"
                                loading="lazy"
                                width="600"
                                height="600"
                            >
                            <div class="vca-overlay-action" aria-hidden="true">
                                <div class="vca-arrow-circle">
                                    <svg class="vca-arrow-icon-diag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7"></line>
                                        <polyline points="7 7 17 7 17 17"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php

        return (string) ob_get_clean();
    }

    private static function parse_csv(string $value): array
    {
        $items = array_map('trim', explode(',', $value));
        $items = array_filter($items, static fn($item) => $item !== '');

        return array_values($items);
    }

    private static function unique_terms(array $terms): array
    {
        $unique = [];

        foreach ($terms as $term) {
            if (!$term instanceof WP_Term) {
                continue;
            }

            $unique[$term->term_id] = $term;
        }

        return array_values($unique);
    }

    private static function find_term_by_slug_or_name(string $value, string $taxonomy): ?WP_Term
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $term = get_term_by('slug', sanitize_title($value), $taxonomy);
        if ($term instanceof WP_Term && !is_wp_error($term)) {
            return $term;
        }

        $term = get_term_by('name', $value, $taxonomy);
        if ($term instanceof WP_Term && !is_wp_error($term)) {
            return $term;
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'name__like' => $value,
            'number'     => 20,
        ]);

        if (!is_wp_error($terms) && is_array($terms)) {
            foreach ($terms as $possible_term) {
                if (
                    $possible_term instanceof WP_Term &&
                    mb_strtolower($possible_term->name) === mb_strtolower($value)
                ) {
                    return $possible_term;
                }
            }
        }

        return null;
    }

}

WA_Category_Grid::init();
