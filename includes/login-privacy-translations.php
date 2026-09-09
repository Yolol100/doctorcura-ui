<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Keep WooCommerce account identifiers email-focused and lost-password
 * responses neutral so public output does not reveal account existence.
 */
add_filter( 'gettext', static function ( string $translated, string $text, string $domain ): string {
    if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || 'woocommerce' !== $domain ) {
        return $translated;
    }

    $language = dcui_current_language();

    $email_label = match ( $language ) {
        'en'    => 'Email address',
        'fr'    => 'Adresse e-mail',
        default => 'E-Mail-Adresse',
    };

    $email_prompt = match ( $language ) {
        'en'    => 'Please enter your email address. You will receive a link to create a new password.',
        'fr'    => 'Veuillez saisir votre adresse e-mail. Vous recevrez un lien pour créer un nouveau mot de passe.',
        default => 'Bitte gib deine E-Mail-Adresse ein. Du erhältst einen Link, um ein neues Passwort zu erstellen.',
    };

    $email_required = match ( $language ) {
        'en'    => 'Please enter your email address.',
        'fr'    => 'Veuillez saisir votre adresse e-mail.',
        default => 'Bitte gib deine E-Mail-Adresse ein.',
    };

    $neutral_reset = match ( $language ) {
        'en'    => 'If the email address you entered is associated with an account, you will receive a password reset link. Please check your inbox and spam or junk folder.',
        'fr'    => 'Si l’adresse e-mail saisie est associée à un compte, vous recevrez un lien de réinitialisation du mot de passe. Vérifiez votre boîte de réception ainsi que vos courriers indésirables.',
        default => 'Wenn die eingegebene E-Mail-Adresse mit einem Konto verknüpft ist, erhältst du einen Link zum Zurücksetzen deines Passworts. Bitte prüfe deinen Posteingang sowie deinen Spam- oder Junk-Ordner.',
    };

    if ( in_array( $text, [ 'Username or email address', 'Username or email' ], true ) ) {
        return $email_label;
    }

    if ( in_array( $text, [
        'Please enter your username or email address. You will receive a link to create a new password via email.',
        'Lost your password? Please enter your email. You will receive a link to create a new password.',
    ], true ) ) {
        return $email_prompt;
    }

    if ( 'Enter a username or email address.' === $text ) {
        return $email_required;
    }

    if ( in_array( $text, [
        'Invalid username or email.',
        'There is no account with that username or email address.',
        'Password reset email has been sent.',
    ], true ) ) {
        return $neutral_reset;
    }

    return $translated;
}, 999, 3 );
