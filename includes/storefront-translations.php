<?php

/**
 * WooCommerce / Elementor knopteksten vertalen
 */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Centrale WooCommerce / Elementor / Stripe vertalingen.
 * De grote map wordt één keer per request opgebouwd en daarna hergebruikt.
 */

function wa_wc_translations(): array {
	static $translations = null;

	if ( is_array( $translations ) ) {
		return $translations;
	}

	$translations = [
		'gettext' => [
			// Cart / checkout / shop
			'View cart' => 'Warenkorb ansehen',
			'Checkout' => 'Weiter zum medizinischen Fragebogen',
			'Proceed to checkout' => 'Weiter zur Kasse',
			'Continue shopping' => 'Weiter einkaufen',
			'Cart updated' => 'Warenkorb aktualisiert',
			'Cart totals' => 'Warenkorbsumme',
			'Add to cart' => 'Behandlung anfordern',
			'Select options' => 'Optionen wählen',
			'Product' => 'Produkt',
			'Products' => 'Produkte',
			'Price' => 'Preis',
			'Quantity' => 'Menge',
			'Subtotal' => 'Zwischensumme',
			'Total' => 'Gesamtsumme',
			'Order' => 'Bestellung',
			'Date' => 'Datum',
			'Status' => 'Status',
			'Actions' => 'Aktionen',
			'View' => 'Ansehen',
			'Orders' => 'Bestellungen',
			'Addresses' => 'Adressen',
			'Account details' => 'Kontodetails',
			'Dashboard' => 'Übersicht',
			'Shipment' => 'Versand',
			'Shipping' => 'Versand',
			'Shipping to' => 'Versand an',
			'Billing details' => 'Rechnungsdetails',
			'Your order' => 'Deine Bestellübersicht',
			'Remove item' => 'Artikel entfernen',
			'Remove this item' => 'Diesen Artikel entfernen',
			'Coupon code' => 'Gutscheincode',
			'Apply coupon' => 'Gutschein anwenden',
			'Country / Region' => 'Land / Region',
			'Select a country / region…' => 'Land / Region auswählen…',
			'Select a country / region...' => 'Land / Region auswählen…',
			'Town / City' => 'Stadt',
			'Postcode / ZIP' => 'Postleitzahl',
			'Province' => 'Bundesland',
			'Select an option…' => 'Option auswählen…',
			'Select an option...' => 'Option auswählen…',
			'Update country / region' => 'Land / Region aktualisieren',
			'Update totals' => 'Gesamtsumme aktualisieren',

			'Cart' => 'Warenkorb',
			'Shopping cart' => 'Warenkorb',
			'Update cart' => 'Warenkorb aktualisiert',
			'Have a coupon?' => 'Hast du einen Gutscheincode?',
			'Click here to enter your code' => 'Hier klicken, um deinen Code einzugeben',
			'Payment' => 'Zahlung',
			'Order received' => 'Bestellung erhalten',
			'Thank you. Your order has been received.' => 'Vielen Dank. Deine Bestellung ist eingegangen.',
			'Order number:' => 'Bestellnummer:',
			'Email:' => 'E-Mail-Adresse:',
			'Phone:' => 'Telefonnummer:',
			'Method:' => 'Methode:',
			'Shipping method' => 'Versandart',
			'Payment method' => 'Zahlungsmethode',
			'Order details' => 'Bestelldetails',
			'Related products' => 'Ähnliche Produkte',
			'Description' => 'Beschreibung',
			'Additional information' => 'Zusätzliche Informationen',
			'Reviews' => 'Bewertungen',
			'SKU' => 'Artikelnummer',
			'In stock' => 'Auf Lager',
			'Out of stock' => 'Nicht auf Lager',
			'Sale!' => 'Angebot!',
			'Read more' => 'Mehr erfahren',
			'View products' => 'Produkte ansehen',
			'Default sorting' => 'Standardsortierung',
			'Sort by popularity' => 'Nach Beliebtheit sortieren',
			'Sort by average rating' => 'Nach durchschnittlicher Bewertung sortieren',
			'Sort by latest' => 'Nach Neuheit sortieren',
			'Sort by price: low to high' => 'Nach Preis sortieren: aufsteigend',
			'Sort by price: high to low' => 'Nach Preis sortieren: absteigend',
			'No products were found matching your selection.' => 'Es wurden keine Produkte gefunden, die deiner Auswahl entsprechen.',

			// Betalen / Stripe
			'Save Card' => 'Karte speichern',
			'Save payment method' => 'Zahlungsmethode speichern',
			'Popular payment methods' => 'Beliebte Zahlungsmethoden',
			'Google Pay selected' => 'Google Pay ausgewählt',
			'Apple Pay selected' => 'Apple Pay ausgewählt',
			'Pay with PromptPay' => 'Mit PromptPay bezahlen',
			'Pay with Klarna' => 'Mit Klarna bezahlen',
			'Pay with Bancontact' => 'Mit Bancontact bezahlen',
			'Pay with SEPA' => 'Mit SEPA bezahlen',
			'Pay with WeChat' => 'Mit WeChat bezahlen',
			'Pay with MobilePay' => 'Mit MobilePay bezahlen',
			'Pay with PayNow' => 'Mit PayNow bezahlen',
			'Pay now' => 'Jetzt bezahlen',
			'Processing payment' => 'Zahlung wird verarbeitet',
			'Payment failed' => 'Zahlung fehlgeschlagen',
			'Your card was declined' => 'Deine Karte wurde abgelehnt',
			'privacy policy' => 'Datenschutzerklärung',

			// Account / login / reset
			'This key is invalid or has already been used. Please reset your password again if needed.' => 'Dieser Schlüssel ist ungültig oder wurde bereits verwendet. Bitte setze dein Passwort bei Bedarf erneut zurück.',
			'Lost your password? Please enter your email. You will receive a link to create a new password.' => 'Passwort vergessen? Bitte gib deine E-Mail-Adresse ein. Du erhältst einen Link, um ein neues Passwort zu erstellen.',
			'Lost your password?' => 'Passwort vergessen?',
			'Email' => 'E-Mail-Adresse',
			'Required' => 'Erforderlich',
			'Reset password' => 'Passwort zurücksetzen',
			'Remember Me' => 'Angemeldet bleiben',
			'Login' => 'Anmelden',
			'Password' => 'Passwort',
			'Show password' => 'Passwort anzeigen',
			'Log in' => 'Anmelden',
			'Register' => 'Registrieren',
			'You will receive further instructions by email.' => 'Du erhältst weitere Anweisungen per E-Mail.',
			'Password reset email has been sent.' => 'Die E-Mail zum Zurücksetzen des Passworts wurde gesendet.',
			'If the email address you entered is associated with an account, you will receive a password reset link. Please check your inbox and spam or junk folder.' => 'Wenn die eingegebene E-Mail-Adresse mit einem Konto verknüpft ist, erhältst du einen Link zum Zurücksetzen deines Passworts. Bitte prüfe deinen Posteingang sowie deinen Spam- oder Junk-Ordner.',
			'No order has been made yet.' => 'Du hast noch keine Bestellungen aufgegeben.',
			'Ship to a different address?' => 'Lieferadresse anstelle der Rechnungsadresse verwenden',
			'Ship to a different address' => 'Lieferadresse anstelle der Rechnungsadresse verwenden',
			'Browse products' => 'Produkte ansehen',
			'No downloads available yet.' => 'Noch keine Downloads verfügbar.',
			'The following addresses will be used on the checkout page by default.' => 'Die folgenden Adressen werden standardmäßig auf der Checkout-Seite verwendet.',
			'Billing address' => 'Rechnungsadresse',
			'Add Billing address' => 'Rechnungsadresse hinzufügen',
			'Shipping address' => 'Lieferadresse',
			'Add Shipping address' => 'Lieferadresse hinzufügen',
			'You have not set up this type of address yet.' => 'Du hast diese Art von Adresse noch nicht eingerichtet.',
			'No saved methods found.' => 'Keine gespeicherten Zahlungsmethoden gefunden.',
			'Add payment method' => 'Zahlungsmethode hinzufügen',
			'Payment methods' => 'Zahlungsmethoden',
			'Downloads' => 'Downloads',
			'Your cart is currently empty.' => 'Dein Warenkorb ist derzeit leer.',
			'Return to shop' => 'Zurück zum Shop',
			'First name' => 'Vorname',
			'Last name' => 'Nachname',
			'Display name' => 'Anzeigename',
			'This will be how your name will be displayed in the account section and in reviews' => 'So wird dein Name im Kontobereich und in Bewertungen angezeigt',
			'Password change' => 'Passwort ändern',
			'Current password (leave blank to leave unchanged)' => 'Aktuelles Passwort (leer lassen, um es nicht zu ändern)',
			'New password (leave blank to leave unchanged)' => 'Neues Passwort (leer lassen, um es nicht zu ändern)',
			'Confirm new password' => 'Neues Passwort bestätigen',
			'Save changes' => 'Änderungen speichern',
			'From' => 'ab',
			'From price:' => 'Ab Preis:',
			'recent orders' => 'letzten Bestellungen',
			'shipping and billing addresses' => 'Liefer- und Rechnungsadressen',
			'edit your password and account details' => 'dein Passwort und deine Kontodaten bearbeiten',

			// Variabele producten
			'This product has multiple variants. The options may be chosen on the product page' => 'Dieses Produkt hat mehrere Varianten. Die Optionen können auf der Produktseite gewählt werden.',

			// Checkout / betaaluitleg
			'Click the "Google Pay" button to submit your payment information and complete your order.' => 'Klicke auf die Schaltfläche „Google Pay“, um deine Zahlungsinformationen zu übermitteln und deine Bestellung abzuschließen.',
			'Click the "Apple Pay" button to submit your payment information and complete your order.' => 'Klicke auf die Schaltfläche „Apple Pay“, um deine Zahlungsinformationen zu übermitteln und deine Bestellung abzuschließen.',
			'Click Pay with PromptPay and you will be shown a QR code.' => 'Klicke auf „Mit PromptPay bezahlen“ und dir wird ein QR-Code angezeigt.',
			'Scan the QR code using a payment app that supports PromptPay.' => 'Scanne den QR-Code mit einer Zahlungs-App, die PromptPay unterstützt.',
			'The authentication process may take several moments. Once confirmed, you will be redirected to the order received page.' => 'Die Authentifizierung kann einen Moment dauern. Nach der Bestätigung wirst du zur Bestellbestätigungsseite weitergeleitet.',
			'Since your browser does not support JavaScript, or it is disabled, please ensure you click the Update Totals button before placing your order. You may be charged more than the amount stated above if you fail to do so.' => 'Da dein Browser JavaScript nicht unterstützt oder es deaktiviert ist, stelle bitte sicher, dass du vor dem Absenden deiner Bestellung auf die Schaltfläche „Gesamtsumme aktualisieren“ klickst. Andernfalls könnte dir ein höherer Betrag als oben angegeben berechnet werden.',

			// Validatie
			'Please fill in this field.' => 'Bitte fülle dieses Feld aus.',
			'Please enter a valid email address' => 'Bitte gib eine gültige E-Mail-Adresse ein.',

			// Overig
			'Log out' => 'Abmelden',
		],

		'checkout_specific' => [
			'Product' => 'Behandlung',
			'Place order' => 'Anfrage absenden',
			'Ship to a different address?' => 'Lieferadresse anstelle der Rechnungsadresse verwenden',
			'Ship to a different address' => 'Lieferadresse anstelle der Rechnungsadresse verwenden',
		],

		'field_labels' => [
			'billing_country' => 'Land / Region',
			'billing_city' => 'Stadt',
			'billing_postcode' => 'Postleitzahl',
			'billing_state' => 'Bundesland',
			'shipping_country' => 'Land / Region',
			'shipping_city' => 'Stadt',
			'shipping_postcode' => 'Postleitzahl',
			'shipping_state' => 'Bundesland',
			'order_comments' => 'Zusätzliche Hinweise für Ihren Arzt',
			'account_first_name' => 'Vorname',
			'account_last_name' => 'Nachname',
			'account_display_name' => 'Anzeigename',
			'account_email' => 'E-Mail-Adresse',
			'password_current' => 'Aktuelles Passwort (leer lassen, um es nicht zu ändern)',
			'password_1' => 'Neues Passwort (leer lassen, um es nicht zu ändern)',
			'password_2' => 'Neues Passwort bestätigen',
		],

		'field_placeholders' => [
			'coupon_code' => 'Gutscheincode',
			'order_comments' => 'Hinweise zu Ihrer Bestellung, z. B. besondere Lieferhinweise.',
			'billing_country' => 'Land / Region auswählen…',
			'billing_state' => 'Option auswählen…',
			'shipping_country' => 'Land / Region auswählen…',
			'shipping_state' => 'Option auswählen…',
		],
	];

	return $translations;
}

function wa_wc_t( string $key, string $fallback = '' ): string {
	if ( 'de' !== dcui_current_language() ) {
		return '' !== $fallback ? $fallback : $key;
	}

	$data = wa_wc_translations();
	return $data['gettext'][ trim( $key ) ] ?? $fallback;
}

/**
 * Algemene vertaling via gettext.
 */
add_filter( 'gettext', 'wa_wc_translate_strings', 20, 3 );
function wa_wc_translate_strings( $translated, $text, $domain ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || 'de' !== dcui_current_language() ) {
		return $translated;
	}

	$allowed_domains = [ 'woocommerce', 'default', 'elementor', 'header-footer-elementor', 'woo-stripe-payment' ];

	if ( ! in_array( $domain, $allowed_domains, true ) ) {
		return $translated;
	}

	$data = wa_wc_translations();
	$text = is_string( $text ) ? trim( $text ) : $text;

	if ( function_exists( 'is_checkout' ) && is_checkout() && isset( $data['checkout_specific'][ $text ] ) ) {
		return $data['checkout_specific'][ $text ];
	}

	return $data['gettext'][ $text ] ?? $translated;
}

add_filter( 'ngettext', 'wa_wc_translate_nstrings', 20, 5 );
function wa_wc_translate_nstrings( $translated, $single, $plural, $number, $domain ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || 'de' !== dcui_current_language() ) {
		return $translated;
	}

	$allowed_domains = [ 'woocommerce', 'default', 'elementor', 'header-footer-elementor', 'woo-stripe-payment' ];

	if ( ! in_array( $domain, $allowed_domains, true ) ) {
		return $translated;
	}

	$data = wa_wc_translations();
	$single = is_string( $single ) ? trim( $single ) : $single;

	if ( function_exists( 'is_checkout' ) && is_checkout() && isset( $data['checkout_specific'][ $single ] ) ) {
		return $data['checkout_specific'][ $single ];
	}

	return $data['gettext'][ $single ] ?? $translated;
}

/**
 * Forceer WooCommerce My Account menu labels.
 * Dit is stabieler dan alleen gettext voor account-menu-items.
 */
add_filter( 'woocommerce_account_menu_items', 'wa_wc_fix_account_menu_labels', 99 );
function wa_wc_fix_account_menu_labels( $items ) {
	if ( 'de' !== dcui_current_language() ) {
		return $items;
	}

	$map = [
		'dashboard'       => 'Übersicht',
		'orders'          => 'Bestellungen',
		'downloads'       => 'Downloads',
		'edit-address'    => 'Adressen',
		'payment-methods' => 'Zahlungsmethoden',
		'edit-account'    => 'Kontoinformationen',
		'customer-logout' => 'Abmelden',
	];

	foreach ( $map as $key => $label ) {
		if ( isset( $items[ $key ] ) ) {
			$items[ $key ] = $label;
		}
	}

	return $items;
}

/**
 * Checkout velden aanpassen.
 */
add_filter( 'woocommerce_checkout_fields', 'wa_wc_customize_checkout_fields', 20 );
function wa_wc_customize_checkout_fields( $fields ) {
	if ( 'de' !== dcui_current_language() ) {
		return $fields;
	}

	$data = wa_wc_translations();

	$billing_label_map = [
		'billing_country'  => 'billing_country',
		'billing_city'     => 'billing_city',
		'billing_postcode' => 'billing_postcode',
		'billing_state'    => 'billing_state',
	];

	foreach ( $billing_label_map as $field_key => $translation_key ) {
		if ( isset( $fields['billing'][ $field_key ] ) ) {
			$fields['billing'][ $field_key ]['label'] = $data['field_labels'][ $translation_key ];
		}
	}

	$shipping_label_map = [
		'shipping_country'  => 'shipping_country',
		'shipping_city'     => 'shipping_city',
		'shipping_postcode' => 'shipping_postcode',
		'shipping_state'    => 'shipping_state',
	];

	foreach ( $shipping_label_map as $field_key => $translation_key ) {
		if ( isset( $fields['shipping'][ $field_key ] ) ) {
			$fields['shipping'][ $field_key ]['label'] = $data['field_labels'][ $translation_key ];
		}
	}

	if ( isset( $fields['billing']['billing_country'] ) ) {
		$fields['billing']['billing_country']['placeholder'] = $data['field_placeholders']['billing_country'];
	}

	if ( isset( $fields['billing']['billing_state'] ) ) {
		$fields['billing']['billing_state']['placeholder'] = $data['field_placeholders']['billing_state'];
	}

	if ( isset( $fields['shipping']['shipping_country'] ) ) {
		$fields['shipping']['shipping_country']['placeholder'] = $data['field_placeholders']['shipping_country'];
	}

	if ( isset( $fields['shipping']['shipping_state'] ) ) {
		$fields['shipping']['shipping_state']['placeholder'] = $data['field_placeholders']['shipping_state'];
	}

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = $data['field_labels']['order_comments'];
		$fields['order']['order_comments']['placeholder'] = $data['field_placeholders']['order_comments'];
	}

	return $fields;
}

add_filter( 'woocommerce_coupon_code_placeholder', 'wa_wc_coupon_placeholder' );
function wa_wc_coupon_placeholder( $placeholder ) {
	if ( 'de' !== dcui_current_language() ) {
		return $placeholder;
	}

	$data = wa_wc_translations();
	return $data['field_placeholders']['coupon_code'] ?? $placeholder;
}

/**
 * Knoppen add to cart.
 */
add_filter( 'woocommerce_product_add_to_cart_text', 'wa_wc_loop_cart_button_text', 20, 2 );
function wa_wc_loop_cart_button_text( $text, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $text;
	}

	if ( $product->is_type( 'variable' ) ) {
		return wa_wc_t( 'Select options', (string) $text );
	}

	return wa_wc_t( 'Add to cart', (string) $text );
}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'wa_wc_single_cart_button_text', 20, 2 );
function wa_wc_single_cart_button_text( $text, $product ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $text;
	}

	return wa_wc_t( 'Add to cart', (string) $text );
}

/**
 * "ab Preis:" noot naast prijs voor variabele producten.
 */
add_action( 'woocommerce_after_shop_loop_item_title', 'dcura_price_note_next_to_price', 11 );
function dcura_price_note_next_to_price() {
	global $product;

	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return;
	}

	$label = dcui_text( [
		'de' => 'Ab Preis:',
		'en' => 'From price:',
		'fr' => 'À partir de :',
	] );

	echo '<span class="dcura-price-inline-note">' . esc_html( $label ) . '</span>';
}

add_action( 'wp_enqueue_scripts', 'dcui_translation_assets', 20 );
function dcui_translation_assets() {
    if ( ! function_exists( 'is_woocommerce' ) || ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) || ! function_exists( 'is_account_page' ) ) {
        return;
    }

    $in_woocommerce_context = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
    if ( ! $in_woocommerce_context ) {
        return;
    }

    wp_enqueue_style(
        'dcui-storefront-translations',
        DCUI_URL . 'assets/css/storefront-translations.css',
        [],
        DCUI_VERSION
    );

    if ( 'de' !== dcui_current_language() || ( ! is_cart() && ! is_checkout() && ! is_account_page() ) ) {
        return;
    }

    wp_enqueue_script(
        'dcui-storefront-translations',
        DCUI_URL . 'assets/js/storefront-translations.js',
        [],
        DCUI_VERSION,
        true
    );

    wp_localize_script(
        'dcui-storefront-translations',
        'DCUITranslations',
        [
            'replacements' => [
                'Required' => 'Erforderlich',
                'Reset password' => 'Passwort zurücksetzen',
                'Save changes' => 'Änderungen speichern',
                'Pay now' => 'Jetzt bezahlen',
                'Update totals' => 'Gesamtsumme aktualisieren',
                'If the email address you entered is associated with an account, you will receive a password reset link. Please check your inbox and spam or junk folder.' => 'Wenn die eingegebene E-Mail-Adresse mit einem Konto verknüpft ist, erhältst du einen Link zum Zurücksetzen deines Passworts. Bitte prüfe deinen Posteingang sowie deinen Spam- oder Junk-Ordner.',
                'No order has been made yet.' => 'Du hast noch keine Bestellungen aufgegeben.',
                'Ship to a different address?' => 'Lieferadresse anstelle der Rechnungsadresse verwenden',
                'Ship to a different address' => 'Lieferadresse anstelle der Rechnungsadresse verwenden',
            ],
        ]
    );
}
