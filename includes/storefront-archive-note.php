<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stop emails
 */
/**
 * Disable admin email notifications for user account changes
 * (WordPress + WooCommerce)
 */

/**
 * 1. Disable admin email when a user changes their password
 */
add_filter( 'wp_password_change_notification_email', '__return_false' );

/**
 * 2. Disable admin email when a user updates their email address
 */
add_filter( 'send_email_change_email', '__return_false' );

/**
 * 3. Disable admin email when a new user account is created
 * (WooCommerce uses this too)
 */
add_filter( 'wp_new_user_notification_email_admin', '__return_false' );

/**
 * WooCommerce archive price note
 */
add_action( 'woocommerce_after_shop_loop_item', 'dcura_price_note_below_button', 20 );

function dcura_price_note_below_button() {
	echo '<div class="dcura-archive-extra-info">';
		echo '<p class="dcura-price-note">' . esc_html( dcui_text( [
            'de' => 'Alle angezeigten Preise beinhalten ein eConsult, ein ärztliches Rezept und die 24/7 klinische Unterstützung.',
            'en' => 'All displayed prices include an eConsult, a medical prescription and 24/7 clinical support.',
            'fr' => 'Tous les prix affichés incluent une eConsultation, une ordonnance médicale et une assistance clinique 24 h/24 et 7 j/7.',
        ] ) ) . '</p>';
	echo '</div>';
}

add_action( 'wp_enqueue_scripts', 'dcura_price_note_styles', 20 );

function dcura_price_note_styles() {
	wp_enqueue_style( 'dcui-storefront-translations' );
}
