<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'woocommerce_after_shop_loop_item', 'dcura_price_note_below_button', 20 );

function dcura_price_note_below_button(): void {
    dcura_price_note_styles();

    echo '<div class="dcura-archive-extra-info">';
    echo '<p class="dcura-price-note">' . esc_html(
        dcui_text(
            [
                'de' => 'Alle angezeigten Preise beinhalten ein eConsult, ein ärztliches Rezept und die 24/7 klinische Unterstützung.',
                'en' => 'All displayed prices include an eConsult, a medical prescription and 24/7 clinical support.',
                'fr' => 'Tous les prix affichés incluent une eConsultation, une ordonnance médicale et une assistance clinique 24 h/24 et 7 j/7.',
            ]
        )
    ) . '</p>';
    echo '</div>';
}

function dcura_price_note_styles(): void {
    wp_enqueue_style( 'dcui-storefront-translations', DCUI_URL . 'assets/css/storefront-translations.css', [], DCUI_VERSION );
}
