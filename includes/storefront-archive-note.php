<?php

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
		echo '<p class="dcura-price-note">' . esc_html__( 'Alle angezeigten Preise beinhalten ein eConsult, ein ärztliches Rezept und die 24/7 klinische Unterstützung.', 'doctorcura' ) . '</p>';
	echo '</div>';
}


add_action( 'wp_enqueue_scripts', 'dcura_price_note_styles', 20 );

function dcura_price_note_styles() {
	wp_enqueue_style( 'dcui-storefront-translations' );
}
