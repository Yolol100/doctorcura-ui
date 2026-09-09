<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class WA_Category_Grid {
    public static function init(): void {
        add_shortcode( 'wa_category_grid', [ __CLASS__, 'render_grid' ] );
        add_shortcode( 'alle_categorieen', [ __CLASS__, 'render_grid' ] );
    }

    public static function render_grid( $atts = [] ): string {
        $atts = shortcode_atts(
            [
                'category_ids' => '',
                'categories'   => '',
                'hide_empty'   => 'true',
                'exclude'      => 'uncategorized,alle-behandlungen,alle-medikamente,mann,frau',
                'context'      => 'auto',
            ],
            is_array( $atts ) ? $atts : [],
            'wa_category_grid'
        );

        $hide_empty = filter_var( $atts['hide_empty'], FILTER_VALIDATE_BOOLEAN );
        $context    = self::resolve_context( (string) $atts['context'] );
        $terms      = self::resolve_terms( $atts, $hide_empty );
        $exclude    = self::parse_csv( (string) $atts['exclude'] );
        $items      = [];

        foreach ( self::unique_terms( $terms ) as $term ) {
            if ( in_array( $term->slug, $exclude, true ) || ( $hide_empty && (int) $term->count < 1 ) ) {
                continue;
            }

            $for_man   = 'yes' === get_term_meta( $term->term_id, '_cat_is_for_man', true );
            $for_woman = 'yes' === get_term_meta( $term->term_id, '_cat_is_for_woman', true );

            if ( ( 'man' === $context && ! $for_man ) || ( 'woman' === $context && ! $for_woman ) ) {
                continue;
            }

            $link = get_term_link( $term );
            if ( is_wp_error( $link ) ) {
                continue;
            }

            $items[] = [
                'term'      => $term,
                'link'      => (string) $link,
                'image_id'  => self::image_id( $term->term_id, $context ),
                'for_man'   => $for_man,
                'for_woman' => $for_woman,
            ];
        }

        if ( ! $items ) {
            return '';
        }

        wp_enqueue_style( 'dcui-category-grid', DCUI_URL . 'assets/css/storefront-category-grid.css', [], DCUI_VERSION );
        $aria = dcui_text( [ 'de' => 'Produktkategorien', 'en' => 'Product categories', 'fr' => 'Catégories de produits' ] );
        $man_label = dcui_text( [ 'de' => 'Für Männer', 'en' => 'For men', 'fr' => 'Pour hommes' ] );
        $woman_label = dcui_text( [ 'de' => 'Für Frauen', 'en' => 'For women', 'fr' => 'Pour femmes' ] );

        ob_start(); ?>
        <nav class="vca-grid-wrapper" aria-label="<?php echo esc_attr( $aria ); ?>">
            <div class="vca-layout">
                <?php foreach ( $items as $item ) : $term = $item['term']; ?>
                    <a href="<?php echo esc_url( $item['link'] ); ?>" class="vca-item-link dc-grid-item dc-grid-link">
                        <h3 class="vca-item-title dc-grid-title"><?php echo esc_html( $term->name ); ?></h3>
                        <div class="vca-image-box dc-img-wrapper">
                            <?php echo self::render_image( (int) $item['image_id'], $term->name ); ?>
                            <?php if ( $item['for_man'] || $item['for_woman'] ) : ?>
                                <div class="vca-badge-container dc-badge-container">
                                    <?php if ( $item['for_man'] ) : ?><span class="vca-badge vca-badge-male dc-badge male"><span class="screen-reader-text"><?php echo esc_html( $man_label ); ?></span><?php echo self::gender_svg( 'male' ); ?></span><?php endif; ?>
                                    <?php if ( $item['for_woman'] ) : ?><span class="vca-badge vca-badge-female dc-badge female"><span class="screen-reader-text"><?php echo esc_html( $woman_label ); ?></span><?php echo self::gender_svg( 'female' ); ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="vca-overlay-action dc-arrow-overlay" aria-hidden="true"><div class="vca-arrow-circle dc-arrow-circle"><svg class="vca-arrow-icon-diag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg></div></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php return (string) ob_get_clean();
    }

    private static function resolve_terms( array $atts, bool $hide_empty ): array {
        $ids = array_filter( array_map( 'absint', explode( ',', (string) $atts['category_ids'] ) ) );
        if ( $ids ) {
            $terms = [];
            foreach ( $ids as $id ) {
                $term = get_term( $id, 'product_cat' );
                if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
                    $terms[] = $term;
                }
            }
            return $terms;
        }

        $requested = self::parse_csv( (string) $atts['categories'] );
        if ( $requested ) {
            $terms = [];
            foreach ( $requested as $value ) {
                $term = self::find_term( $value );
                if ( $term ) {
                    $terms[] = $term;
                }
            }
            return $terms;
        }

        $terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => $hide_empty, 'orderby' => 'name', 'order' => 'ASC' ] );
        return is_array( $terms ) && ! is_wp_error( $terms ) ? $terms : [];
    }

    private static function resolve_context( string $context ): string {
        $context = strtolower( trim( $context ) );
        if ( in_array( $context, [ 'man', 'mann', 'male' ], true ) ) return 'man';
        if ( in_array( $context, [ 'woman', 'frau', 'female' ], true ) ) return 'woman';
        if ( 'auto' === $context ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term && 'product_cat' === $term->taxonomy ) {
                if ( 'mann' === $term->slug ) return 'man';
                if ( 'frau' === $term->slug ) return 'woman';
            }
        }
        return 'all';
    }

    private static function image_id( int $term_id, string $context ): int {
        $key = 'man' === $context ? 'dc_img_id_male' : ( 'woman' === $context ? 'dc_img_id_female' : '' );
        $id = $key ? absint( get_term_meta( $term_id, $key, true ) ) : 0;
        return $id ?: absint( get_term_meta( $term_id, 'thumbnail_id', true ) );
    }

    private static function render_image( int $id, string $alt ): string {
        if ( $id ) {
            return (string) wp_get_attachment_image( $id, 'medium_large', false, [ 'class' => 'vca-cat-img dc-cat-img', 'loading' => 'lazy', 'alt' => $alt ] );
        }
        return '<img src="' . esc_url( wc_placeholder_img_src() ) . '" class="vca-cat-img dc-cat-img" alt="' . esc_attr( $alt ) . '" loading="lazy">';
    }

    private static function gender_svg( string $type ): string {
        if ( 'female' === $type ) {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="4" r="2"/><path d="M14.9,7.1C14.1,6.4,13.1,6,12,6s-2.1.4-2.9,1.1C8.4,7.8,8,8.8,8,10c0,1,.3,1.9.7,2.7L10,16v6h4v-6l1.3-3.3c.4-.8.7-1.7.7-2.7 0-1.2-.4-2.2-1.1-2.9z"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="4" r="2"/><path d="M15,7H9C7.9,7,7,7.9,7,9v6h2v7h2v-7h2v7h2v-7h2V9c0-1.1-.9-2-2-2z"/></svg>';
    }

    private static function parse_csv( string $value ): array {
        return array_values( array_filter( array_map( 'trim', explode( ',', $value ) ), static fn( $v ) => '' !== $v ) );
    }

    private static function unique_terms( array $terms ): array {
        $unique = [];
        foreach ( $terms as $term ) if ( $term instanceof WP_Term ) $unique[ $term->term_id ] = $term;
        return array_values( $unique );
    }

    private static function find_term( string $value ): ?WP_Term {
        $value = trim( $value );
        if ( '' === $value ) return null;
        foreach ( [ [ 'slug', sanitize_title( $value ) ], [ 'name', $value ] ] as [ $field, $needle ] ) {
            $term = get_term_by( $field, $needle, 'product_cat' );
            if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) return $term;
        }
        return null;
    }
}

WA_Category_Grid::init();
require_once __DIR__ . '/storefront-category-grid-admin.php';
