<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Use a neutral login identifier label so the form does not emphasize
 * usernames or email addresses. The actual authentication behavior remains unchanged.
 */
add_filter( 'gettext', static function ( string $translated, string $text, string $domain ): string {
    if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
        return $translated;
    }

    if ( 'woocommerce' !== $domain ) {
        return $translated;
    }

    if ( ! in_array( $text, [ 'Username or email address', 'Username or email' ], true ) ) {
        return $translated;
    }

    return match ( dcui_current_language() ) {
        'en'    => 'Account',
        'fr'    => 'Compte',
        default => 'Konto',
    };
}, 999, 3 );
