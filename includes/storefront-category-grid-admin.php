<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;

final class DCUI_Category_Grid_Admin {
    public static function init(): void {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'assets' ] );
        add_action( 'product_cat_add_form_fields', [ __CLASS__, 'add_fields' ] );
        add_action( 'product_cat_edit_form_fields', [ __CLASS__, 'edit_fields' ], 10, 2 );
        add_action( 'edited_product_cat', [ __CLASS__, 'save' ] );
        add_action( 'create_product_cat', [ __CLASS__, 'save' ] );
    }

    public static function assets( string $hook ): void {
        if ( ! in_array( $hook, [ 'term.php', 'edit-tags.php' ], true ) ) return;
        $taxonomy = isset( $_GET['taxonomy'] ) && is_string( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
        if ( 'product_cat' !== $taxonomy ) return;
        wp_enqueue_media();
        wp_enqueue_script( 'dcui-category-grid-admin', DCUI_URL . 'assets/js/category-grid-admin.js', [ 'jquery' ], DCUI_VERSION, true );
        wp_localize_script( 'dcui-category-grid-admin', 'DCUICategoryGridAdmin', [ 'noneText' => __( 'None', 'doctorcura-ui' ) ] );
    }

    public static function add_fields(): void {
        wp_nonce_field( 'dcui_category_meta_save', 'dcui_category_meta_nonce' ); ?>
        <div class="form-field"><label><?php esc_html_e( 'Gender labels', 'doctorcura-ui' ); ?></label><label><input type="checkbox" name="_cat_is_for_man" value="yes"> <?php esc_html_e( 'For men', 'doctorcura-ui' ); ?></label><br><label><input type="checkbox" name="_cat_is_for_woman" value="yes"> <?php esc_html_e( 'For women', 'doctorcura-ui' ); ?></label></div>
        <div class="form-field"><label><?php esc_html_e( 'Specific category images', 'doctorcura-ui' ); ?></label><?php self::picker( 'm', 0, __( 'Men', 'doctorcura-ui' ) ); self::picker( 'f', 0, __( 'Women', 'doctorcura-ui' ) ); ?></div>
        <?php
    }

    public static function edit_fields( WP_Term $term ): void {
        wp_nonce_field( 'dcui_category_meta_save', 'dcui_category_meta_nonce' );
        $man = get_term_meta( $term->term_id, '_cat_is_for_man', true );
        $woman = get_term_meta( $term->term_id, '_cat_is_for_woman', true ); ?>
        <tr class="form-field"><th scope="row"><?php esc_html_e( 'Gender labels', 'doctorcura-ui' ); ?></th><td><label><input type="checkbox" name="_cat_is_for_man" value="yes" <?php checked( $man, 'yes' ); ?>> <?php esc_html_e( 'For men', 'doctorcura-ui' ); ?></label><br><label><input type="checkbox" name="_cat_is_for_woman" value="yes" <?php checked( $woman, 'yes' ); ?>> <?php esc_html_e( 'For women', 'doctorcura-ui' ); ?></label></td></tr>
        <tr class="form-field"><th scope="row"><?php esc_html_e( 'Specific category images', 'doctorcura-ui' ); ?></th><td><?php self::picker( 'm', absint( get_term_meta( $term->term_id, 'dc_img_id_male', true ) ), __( 'Men', 'doctorcura-ui' ) ); self::picker( 'f', absint( get_term_meta( $term->term_id, 'dc_img_id_female', true ) ), __( 'Women', 'doctorcura-ui' ) ); ?></td></tr>
        <?php
    }

    private static function picker( string $type, int $id, string $label ): void {
        $field = 'm' === $type ? 'dc_img_id_male' : 'dc_img_id_female'; ?>
        <div class="dcui-category-image-picker"><strong><?php echo esc_html( $label ); ?></strong><div id="<?php echo esc_attr( $type . '_prev' ); ?>" class="dcui-category-image-preview"><?php echo $id ? wp_get_attachment_image( $id, [ 80, 80 ] ) : '<span>' . esc_html__( 'None', 'doctorcura-ui' ) . '</span>'; ?></div><input type="hidden" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( 'dc_' . $type . '_id' ); ?>" value="<?php echo esc_attr( (string) $id ); ?>"><button type="button" class="button dcui-category-image-select" data-type="<?php echo esc_attr( $type ); ?>"><?php esc_html_e( 'Choose image', 'doctorcura-ui' ); ?></button> <button type="button" class="button-link-delete dcui-category-image-remove" data-type="<?php echo esc_attr( $type ); ?>"><?php esc_html_e( 'Remove', 'doctorcura-ui' ); ?></button></div>
        <?php
    }

    public static function save( int $term_id ): void {
        if ( ! current_user_can( 'manage_product_terms' ) ) return;
        $nonce = isset( $_POST['dcui_category_meta_nonce'] ) && is_string( $_POST['dcui_category_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['dcui_category_meta_nonce'] ) ) : '';
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'dcui_category_meta_save' ) ) return;
        update_term_meta( $term_id, '_cat_is_for_man', isset( $_POST['_cat_is_for_man'] ) ? 'yes' : 'no' );
        update_term_meta( $term_id, '_cat_is_for_woman', isset( $_POST['_cat_is_for_woman'] ) ? 'yes' : 'no' );
        update_term_meta( $term_id, 'dc_img_id_male', isset( $_POST['dc_img_id_male'] ) && is_scalar( $_POST['dc_img_id_male'] ) ? absint( wp_unslash( (string) $_POST['dc_img_id_male'] ) ) : 0 );
        update_term_meta( $term_id, 'dc_img_id_female', isset( $_POST['dc_img_id_female'] ) && is_scalar( $_POST['dc_img_id_female'] ) ? absint( wp_unslash( (string) $_POST['dc_img_id_female'] ) ) : 0 );
        delete_transient( 'dc_all_product_cats' );
    }
}

DCUI_Category_Grid_Admin::init();
