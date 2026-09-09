<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Prevent the standard admin notification when a new user is created.
add_filter( 'wp_send_new_user_notification_to_admin', '__return_false' );

// Prevent WordPress from sending the old-address security email after an account email change.
add_filter( 'send_email_change_email', '__return_false' );

// WordPress does not expose a boolean switch for the admin password-change email.
// Keep the documented email-array contract intact and mark only this mail for suppression.
add_filter(
    'wp_password_change_notification_email',
    static function ( array $email, $user, string $blogname ): array {
        unset( $user, $blogname );

        $headers = $email['headers'] ?? [];
        if ( is_string( $headers ) ) {
            $headers = preg_split( '/\r\n|\r|\n/', $headers ) ?: [];
        }
        if ( ! is_array( $headers ) ) {
            $headers = [];
        }

        $headers[]        = 'X-DoctorCura-Suppress: password-change-admin';
        $email['headers'] = $headers;

        return $email;
    },
    10,
    3
);

// Short-circuit only the marked admin password-change message before wp_mail() runs.
add_filter(
    'pre_wp_mail',
    static function ( $return, array $atts ) {
        $headers = $atts['headers'] ?? [];

        if ( is_string( $headers ) ) {
            $headers = preg_split( '/\r\n|\r|\n/', $headers ) ?: [];
        }

        if ( ! is_array( $headers ) ) {
            return $return;
        }

        foreach ( $headers as $header ) {
            if ( is_string( $header ) && 0 === strcasecmp( trim( $header ), 'X-DoctorCura-Suppress: password-change-admin' ) ) {
                return true;
            }
        }

        return $return;
    },
    10,
    2
);
