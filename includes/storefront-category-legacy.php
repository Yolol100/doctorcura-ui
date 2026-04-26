<?php

/**
 * Category
 */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- 1. STYLING: FRONT-END CSS ---
add_action( 'wp_enqueue_scripts', 'dc_output_category_grid_css', 20 );
function dc_output_category_grid_css() {
    wp_enqueue_style( 'dcui-category-legacy', DCUI_URL . 'assets/css/storefront-category-legacy.css', [], DCUI_VERSION );
}

// --- 2. ADMIN SCRIPTS (Media Picker) ---
add_action( 'admin_enqueue_scripts', 'dc_enqueue_admin_category_scripts' );
function dc_enqueue_admin_category_scripts( $hook ) {
    if ( ! in_array( $hook, [ 'term.php', 'edit-tags.php' ], true ) ) {
        return;
    }
    if ( isset( $_GET['taxonomy'] ) && 'product_cat' !== sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) ) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script( 'dcui-category-legacy-admin', DCUI_URL . 'assets/js/category-legacy-admin.js', [ 'jquery' ], DCUI_VERSION, true );
}

// --- 3. ADMIN FIELDS & SAVE ---
add_action( 'product_cat_add_form_fields', 'dc_cat_fields_html_add' );
add_action( 'product_cat_edit_form_fields', 'dc_cat_fields_html_edit', 10, 2 );
add_action( 'edited_product_cat', 'dc_save_cat_meta' );
add_action( 'create_product_cat', 'dc_save_cat_meta' );

function dc_cat_fields_html_add() {
    wp_nonce_field( 'dc_cat_meta_save', 'dc_cat_meta_nonce' );
    echo '<div class="form-field"><label>' . esc_html__('Geschlechts-Labels', 'doctorcura-ui') . '</label>
    <label><input type="checkbox" name="_cat_is_for_man" value="yes"> ' . esc_html__('Symbol für Männer', 'doctorcura-ui') . '</label><br>
    <label><input type="checkbox" name="_cat_is_for_woman" value="yes"> ' . esc_html__('Symbol für Frauen', 'doctorcura-ui') . '</label></div>';
}

function dc_cat_fields_html_edit( $term ) {
    $man = get_term_meta( $term->term_id, '_cat_is_for_man', true );
    $woman = get_term_meta( $term->term_id, '_cat_is_for_woman', true );
    $img_m = get_term_meta( $term->term_id, 'dc_img_id_male', true );
    $img_f = get_term_meta( $term->term_id, 'dc_img_id_female', true );
    wp_nonce_field( 'dc_cat_meta_save', 'dc_cat_meta_nonce' );
    ?>
    <tr class="form-field">
        <th scope="row"><?php esc_html_e('Geschlechts-Labels', 'doctorcura-ui'); ?></th>
        <td>
            <label><input type="checkbox" name="_cat_is_for_man" value="yes" <?php checked($man,'yes');?>> <?php esc_html_e('Mann', 'doctorcura-ui'); ?></label><br>
            <label><input type="checkbox" name="_cat_is_for_woman" value="yes" <?php checked($woman,'yes');?>> <?php esc_html_e('Frau', 'doctorcura-ui'); ?></label>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><?php esc_html_e('Spezifische Bilder', 'doctorcura-ui'); ?></th>
        <td>
            <div style="display:flex; gap:20px;">
                <div><strong><?php esc_html_e('Mann:', 'doctorcura-ui'); ?></strong>
                    <div id="m_prev" style="border:1px dashed #ccc; padding:5px;"><?php echo $img_m ? wp_get_attachment_image($img_m,[80,80]) : esc_html__('Keines', 'doctorcura-ui');?></div>
                    <input type="hidden" name="dc_img_id_male" id="dc_m_id" value="<?php echo esc_attr((string) $img_m);?>">
                    <button type="button" class="dc_u button" data-t="m"><?php esc_html_e('Wählen', 'doctorcura-ui'); ?></button>
                </div>
                <div><strong><?php esc_html_e('Frau:', 'doctorcura-ui'); ?></strong>
                    <div id="f_prev" style="border:1px dashed #ccc; padding:5px;"><?php echo $img_f ? wp_get_attachment_image($img_f,[80,80]) : esc_html__('Keines', 'doctorcura-ui');?></div>
                    <input type="hidden" name="dc_img_id_female" id="dc_f_id" value="<?php echo esc_attr((string) $img_f);?>">
                    <button type="button" class="dc_u button" data-t="f"><?php esc_html_e('Wählen', 'doctorcura-ui'); ?></button>
                </div>
            </div>
            
        </td>
    </tr>
    <?php
}

function dc_save_cat_meta( $term_id ) {
    if ( ! current_user_can( 'manage_product_terms' ) ) {
        return;
    }
    if ( !isset($_POST['dc_cat_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['dc_cat_meta_nonce'])), 'dc_cat_meta_save') ) return;
    update_term_meta( $term_id, '_cat_is_for_man', isset($_POST['_cat_is_for_man']) ? 'yes' : 'no' );
    update_term_meta( $term_id, '_cat_is_for_woman', isset($_POST['_cat_is_for_woman']) ? 'yes' : 'no' );
    update_term_meta( $term_id, 'dc_img_id_male', isset($_POST['dc_img_id_male']) ? absint(wp_unslash((string) $_POST['dc_img_id_male'])) : 0 );
    update_term_meta( $term_id, 'dc_img_id_female', isset($_POST['dc_img_id_female']) ? absint(wp_unslash((string) $_POST['dc_img_id_female'])) : 0 );
    delete_transient( 'dc_all_product_cats' );
}

// --- 4. HELPERS ---
function dc_get_svg( $type ) {
    $svgs = [
        'male'   => '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="4" r="2"/><path d="M15,7H9C7.9,7,7,7.9,7,9v6h2v7h2v-7h2v7h2v-7h2v-6C17,7.9,16.1,7,15,7z"/></svg>',
        'female' => '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="4" r="2"/><path d="M14.9,7.1C14.1,6.4,13.1,6,12,6s-2.1,0.4-2.9,1.1C8.4,7.8,8,8.8,8,10c0,1,0.3,1.9,0.7,2.7L10,16v6h4v-6l1.3-3.3c0.4-0.8,0.7-1.7,0.7-2.7C16,8.8,15.6,7.8,14.9,7.1z"/></svg>',
        'arrow'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7" /><polyline points="7 7 17 7 17 17" /></svg>'
    ];
    return $svgs[$type] ?? '';
}

function dc_get_grid_image( $cat_id, $context ) {
    $key = ($context === 'man') ? 'dc_img_id_male' : (($context === 'woman') ? 'dc_img_id_female' : '');
    $id = $key ? get_term_meta($cat_id, $key, true) : '';
    if (!$id) $id = get_term_meta($cat_id, 'thumbnail_id', true);
    return $id ? wp_get_attachment_image_url($id, 'full') : wc_placeholder_img_src();
}

// --- 5. SHORTCODE ---
add_shortcode( 'alle_categorieen', 'dc_render_category_grid_shortcode' );
function dc_render_category_grid_shortcode() {
    $categories = get_terms(['taxonomy'=>'product_cat','hide_empty'=>true,'orderby'=>'name','order'=>'ASC']);
    if (empty($categories) || is_wp_error($categories)) return '';

    $context = (get_queried_object() instanceof WP_Term) ? get_queried_object()->slug : '';
    ob_start(); ?>
    <div class="dc-category-grid">
        <?php foreach ( $categories as $cat ) : 
            // BIJGEWERKTE EXCLUSIELIJST GEBASEERD OP JOUW DATA
            if (in_array($cat->slug, ['uncategorized', 'mann', 'frau', 'alle-behandlungen', 'alle-medikamente'])) continue;
            
            $man_l = get_term_meta($cat->term_id, '_cat_is_for_man', true);
            $woman_l = get_term_meta($cat->term_id, '_cat_is_for_woman', true);
            
            // Context filtering (voor op de specifieke man/vrouw pagina's)
            if (($context === 'mann' && $man_l !== 'yes') || ($context === 'frau' && $woman_l !== 'yes')) continue;
        ?>
            <div class="dc-grid-item">
                <a href="<?php echo esc_url(get_term_link($cat));?>" class="dc-grid-link">
                    <div class="dc-img-wrapper">
                        <img src="<?php echo esc_url(dc_get_grid_image($cat->term_id, $context));?>" alt="<?php echo esc_attr($cat->name);?>" class="dc-cat-img">
                        <div class="dc-badge-container">
                            <?php if($man_l==='yes') echo '<div class="dc-badge male">'.dc_get_svg('male').'</div>'; ?>
                            <?php if($woman_l==='yes') echo '<div class="dc-badge female">'.dc_get_svg('female').'</div>'; ?>
                        </div>
                        <div class="dc-arrow-overlay"><div class="dc-arrow-circle"><?php echo dc_get_svg('arrow');?></div></div>
                    </div>
                    <h3 class="dc-grid-title"><?php echo esc_html($cat->name);?></h3>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
}
