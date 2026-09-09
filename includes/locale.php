<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'dcui_current_language' ) ) {
    function dcui_current_language(): string {
        static $language = null;

        if ( is_string( $language ) ) {
            return $language;
        }

        $supported = [ 'de', 'en', 'fr' ];

        if ( class_exists( '\\DCAF\\Locale' ) ) {
            $locale    = \DCAF\Locale::get();
            $candidate = strtolower( substr( (string) $locale, 0, 2 ) );

            if ( in_array( $candidate, $supported, true ) ) {
                $language = $candidate;
                return $language;
            }
        }

        if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
            $path      = (string) parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
            $candidate = strtolower( (string) strtok( trim( $path, '/' ), '/' ) );

            if ( in_array( $candidate, $supported, true ) ) {
                $language = $candidate;
                return $language;
            }
        }

        if ( isset( $_COOKIE['googtrans'] ) && is_string( $_COOKIE['googtrans'] ) ) {
            $parts     = explode( '/', trim( sanitize_text_field( wp_unslash( $_COOKIE['googtrans'] ) ), '/' ) );
            $candidate = strtolower( (string) end( $parts ) );

            if ( in_array( $candidate, $supported, true ) ) {
                $language = $candidate;
                return $language;
            }
        }

        if ( isset( $_COOKIE['gtranslate_lang'] ) && is_string( $_COOKIE['gtranslate_lang'] ) ) {
            $candidate = strtolower( trim( sanitize_text_field( wp_unslash( $_COOKIE['gtranslate_lang'] ) ) ) );

            if ( in_array( $candidate, $supported, true ) ) {
                $language = $candidate;
                return $language;
            }
        }

        if ( isset( $_GET['lang'] ) && is_string( $_GET['lang'] ) ) {
            $candidate = strtolower( sanitize_text_field( wp_unslash( $_GET['lang'] ) ) );

            if ( in_array( $candidate, $supported, true ) ) {
                $language = $candidate;
                return $language;
            }
        }

        $locale    = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
        $candidate = strtolower( substr( (string) $locale, 0, 2 ) );
        $language  = in_array( $candidate, $supported, true ) ? $candidate : 'de';

        return $language;
    }
}

if ( ! function_exists( 'dcui_text' ) ) {
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
}
