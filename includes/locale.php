<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function dcui_current_language(): string {
    $supported = [ 'de', 'en', 'fr' ];

    if ( class_exists( '\\DCAF\\Locale' ) ) {
        $locale = \DCAF\Locale::get();
        $language = strtolower( substr( $locale, 0, 2 ) );

        if ( in_array( $language, $supported, true ) ) {
            return $language;
        }
    }

    if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
        $path = (string) parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
        $first = strtolower( (string) strtok( trim( $path, '/' ), '/' ) );

        if ( in_array( $first, $supported, true ) ) {
            return $first;
        }
    }

    $locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
    $language = strtolower( substr( (string) $locale, 0, 2 ) );

    return in_array( $language, $supported, true ) ? $language : 'de';
}

function dcui_text( array $translations ): string {
    $language = dcui_current_language();

    if ( isset( $translations[ $language ] ) && is_string( $translations[ $language ] ) ) {
        return $translations[ $language ];
    }

    if ( isset( $translations['de'] ) && is_string( $translations['de'] ) ) {
        return $translations['de'];
    }

    $fallback = reset( $translations );

    return is_string( $fallback ) ? $fallback : '';
}
