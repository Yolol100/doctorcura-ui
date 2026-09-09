<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class M3_Currency_Switcher {
    private const COOKIE_NAME        = 'm3_selected_currency';
    private const BASE_CURRENCY      = 'CHF';
    private const TARGET_CURRENCY    = 'EUR';
    private const OPTION_KEY         = 'm3_exchange_rate_chf_eur';
    private const DEFAULT_RATE       = 1.06;
    private const COOKIE_DURATION    = 30 * DAY_IN_SECONDS;
    private const CRON_HOOK          = 'm3_update_currency_rate';
    private const AJAX_ACTION        = 'm3_switch_currency';
    private const ALLOWED_CURRENCIES = [ self::BASE_CURRENCY, self::TARGET_CURRENCY ];

    /** @var array<int, true> */
    private array $converted_shipping_rates = [];

    public function __construct() {
        $this->register_cron();
        $this->register_hooks();
        $this->protect_currency_specific_pages_from_shared_page_cache();
    }

    private function register_hooks(): void {
        add_filter( 'woocommerce_currency', [ $this, 'filter_currency' ], 9999 );
        add_filter( 'woocommerce_price_num_decimals', static fn() => 2 );
        add_filter( 'woocommerce_price_decimal_separator', [ $this, 'filter_decimal_separator' ] );
        add_filter( 'woocommerce_price_thousand_separator', [ $this, 'filter_thousand_separator' ] );

        foreach ( [
            'woocommerce_product_get_price',
            'woocommerce_product_get_regular_price',
            'woocommerce_product_get_sale_price',
            'woocommerce_product_variation_get_price',
            'woocommerce_product_variation_get_regular_price',
            'woocommerce_product_variation_get_sale_price',
        ] as $hook ) {
            add_filter( $hook, [ $this, 'convert_product_price' ], 9999, 2 );
        }

        foreach ( [
            'woocommerce_variation_prices_price',
            'woocommerce_variation_prices_regular_price',
            'woocommerce_variation_prices_sale_price',
        ] as $hook ) {
            add_filter( $hook, [ $this, 'convert_variation_cached_price' ], 9999, 3 );
        }

        add_filter( 'woocommerce_get_variation_prices_hash', [ $this, 'variation_price_hash' ], 9999, 3 );
        add_filter( 'woocommerce_cart_shipping_packages', [ $this, 'add_shipping_cache_context' ], 9999 );
        add_filter( 'woocommerce_package_rates', [ $this, 'convert_shipping_rates' ], 9999, 2 );
        add_action( 'woocommerce_cart_calculate_fees', [ $this, 'convert_cart_fees' ], PHP_INT_MAX, 1 );
        add_filter( 'woocommerce_coupon_get_amount', [ $this, 'convert_coupon_amount' ], 9999, 2 );
        add_filter( 'woocommerce_coupon_get_minimum_amount', [ $this, 'convert_plain_amount' ], 9999, 2 );
        add_filter( 'woocommerce_coupon_get_maximum_amount', [ $this, 'convert_plain_amount' ], 9999, 2 );

        add_shortcode( 'currency_switcher', [ $this, 'render_switcher' ] );
        add_action( 'wp', [ $this, 'protect_woocommerce_pages_from_shared_page_cache' ], 0 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'ajax_switch_currency' ] );
        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, [ $this, 'ajax_switch_currency' ] );
    }

    private function register_cron(): void {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', self::CRON_HOOK );
        }

        add_action( self::CRON_HOOK, [ $this, 'update_exchange_rate' ] );
    }

    public function get_currency(): string {
        $candidates = [];

        if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) && is_string( $_COOKIE[ self::COOKIE_NAME ] ) ) {
            $candidates[] = strtoupper( sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) ) );
        }

        if ( function_exists( 'WC' ) && WC()->session ) {
            $stored = WC()->session->get( self::COOKIE_NAME );
            if ( is_string( $stored ) ) {
                $candidates[] = strtoupper( sanitize_text_field( $stored ) );
            }
        }

        foreach ( $candidates as $candidate ) {
            if ( in_array( $candidate, self::ALLOWED_CURRENCIES, true ) ) {
                return $candidate;
            }
        }

        return self::BASE_CURRENCY;
    }

    private function set_currency( string $currency ): void {
        $previous = $this->get_currency();
        $currency = strtoupper( $currency );

        if ( ! in_array( $currency, self::ALLOWED_CURRENCIES, true ) ) {
            $currency = self::BASE_CURRENCY;
        }

        if ( function_exists( 'WC' ) && WC()->session ) {
            WC()->session->set( self::COOKIE_NAME, $currency );
        }

        setcookie(
            self::COOKIE_NAME,
            $currency,
            [
                'expires'  => time() + self::COOKIE_DURATION,
                'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
                'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );

        // Make the changed value visible to the remainder of this request.
        $_COOKIE[ self::COOKIE_NAME ] = $currency;

        if ( $previous !== $currency ) {
            $this->clear_shipping_rate_cache();
        }
    }

    private function clear_shipping_rate_cache(): void {
        if ( ! function_exists( 'WC' ) || ! WC()->session || ! method_exists( WC()->session, 'get_session_data' ) ) {
            return;
        }

        $session_data = WC()->session->get_session_data();
        if ( ! is_array( $session_data ) ) {
            return;
        }

        foreach ( array_keys( $session_data ) as $key ) {
            if ( is_string( $key ) && str_starts_with( $key, 'shipping_for_package_' ) ) {
                WC()->session->__unset( $key );
            }
        }
    }

    private function get_rate(): float {
        $rate = (float) get_option( self::OPTION_KEY, self::DEFAULT_RATE );

        return $rate > 0 ? $rate : self::DEFAULT_RATE;
    }

    private function site_uses_supported_base_currency(): bool {
        $configured = strtoupper( (string) get_option( 'woocommerce_currency', self::BASE_CURRENCY ) );

        return self::BASE_CURRENCY === $configured;
    }

    private function is_store_api_request(): bool {
        if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
            return false;
        }

        $route = isset( $_GET['rest_route'] ) && is_string( $_GET['rest_route'] )
            ? rawurldecode( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ) )
            : '';

        if ( str_starts_with( ltrim( $route, '/' ), 'wc/store/' ) ) {
            return true;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] )
            ? rawurldecode( (string) wp_unslash( $_SERVER['REQUEST_URI'] ) )
            : '';

        return str_contains( $request_uri, '/wc/store/' );
    }

    private function is_conversion_context(): bool {
        if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
            return false;
        }

        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return $this->is_store_api_request();
        }

        // admin-ajax.php is an admin context too. Standard WooCommerce storefront
        // requests use wc-ajax, so blocking admin AJAX prevents accidental writes
        // or editor previews from receiving converted values.
        if ( is_admin() ) {
            return false;
        }

        return true;
    }

    private function should_convert(): bool {
        return $this->site_uses_supported_base_currency()
            && self::TARGET_CURRENCY === $this->get_currency()
            && $this->is_conversion_context();
    }

    private function protect_currency_specific_pages_from_shared_page_cache(): void {
        if ( self::TARGET_CURRENCY !== $this->get_currency() || ! $this->is_conversion_context() ) {
            return;
        }

        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
    }

    public function protect_woocommerce_pages_from_shared_page_cache(): void {
        if ( ! $this->site_uses_supported_base_currency() || ! $this->is_conversion_context() ) {
            return;
        }

        $is_woocommerce_page = ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
            || ( function_exists( 'is_cart' ) && is_cart() )
            || ( function_exists( 'is_checkout' ) && is_checkout() )
            || ( function_exists( 'is_account_page' ) && is_account_page() );

        if ( ! $is_woocommerce_page ) {
            return;
        }

        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }

        if ( function_exists( 'nocache_headers' ) ) {
            nocache_headers();
        }
    }

    private function convert_numeric( $value ) {
        if ( ! $this->should_convert() || '' === $value || null === $value || ! is_numeric( $value ) ) {
            return $value;
        }

        return (float) $value * $this->get_rate();
    }

    public function filter_currency( string $currency ): string {
        if ( ! $this->site_uses_supported_base_currency() || ! $this->is_conversion_context() ) {
            return $currency;
        }

        return $this->get_currency();
    }

    public function convert_product_price( $price, $product ) {
        unset( $product );

        return $this->convert_numeric( $price );
    }

    public function convert_variation_cached_price( $price, $variation, $product ) {
        unset( $variation, $product );

        return $this->convert_numeric( $price );
    }

    public function variation_price_hash( array $hash, $product, bool $for_display ): array {
        unset( $product, $for_display );

        $hash[] = 'dcui:' . $this->get_currency() . ':' . ( $this->should_convert() ? (string) $this->get_rate() : '1' );

        return $hash;
    }

    public function add_shipping_cache_context( array $packages ): array {
        if ( ! $this->site_uses_supported_base_currency() || ! $this->is_conversion_context() ) {
            return $packages;
        }

        $currency = $this->get_currency();
        $rate     = self::TARGET_CURRENCY === $currency ? $this->get_rate() : 1.0;

        foreach ( $packages as $index => $package ) {
            if ( ! is_array( $package ) ) {
                continue;
            }

            // WooCommerce includes package data in its shipping session hash. These
            // keys therefore invalidate cached rates when currency or rate changes.
            $packages[ $index ]['dcui_currency']      = $currency;
            $packages[ $index ]['dcui_exchange_rate'] = (string) $rate;
        }

        return $packages;
    }

    public function convert_shipping_rates( array $rates, array $package ): array {
        unset( $package );

        if ( ! $this->should_convert() ) {
            return $rates;
        }

        $rate = $this->get_rate();

        foreach ( $rates as $shipping_rate ) {
            if ( ! is_object( $shipping_rate ) || ! method_exists( $shipping_rate, 'get_cost' ) || ! method_exists( $shipping_rate, 'set_cost' ) ) {
                continue;
            }

            $object_id = spl_object_id( $shipping_rate );
            if ( isset( $this->converted_shipping_rates[ $object_id ] ) ) {
                continue;
            }

            $shipping_rate->set_cost( (float) $shipping_rate->get_cost() * $rate );

            if ( method_exists( $shipping_rate, 'get_taxes' ) && method_exists( $shipping_rate, 'set_taxes' ) ) {
                $taxes = $shipping_rate->get_taxes();
                if ( is_array( $taxes ) ) {
                    foreach ( $taxes as $tax_id => $tax_value ) {
                        if ( is_numeric( $tax_value ) ) {
                            $taxes[ $tax_id ] = (float) $tax_value * $rate;
                        }
                    }
                    $shipping_rate->set_taxes( $taxes );
                }
            }

            $this->converted_shipping_rates[ $object_id ] = true;
        }

        return $rates;
    }

    public function convert_cart_fees( $cart ): void {
        if ( ! $this->should_convert() || ! is_object( $cart ) || ! method_exists( $cart, 'fees_api' ) ) {
            return;
        }

        $fees_api = $cart->fees_api();
        if ( ! is_object( $fees_api ) || ! method_exists( $fees_api, 'get_fees' ) ) {
            return;
        }

        $rate = $this->get_rate();
        foreach ( $fees_api->get_fees() as $fee ) {
            if ( is_object( $fee ) && isset( $fee->amount ) && is_numeric( $fee->amount ) ) {
                $fee->amount = (float) $fee->amount * $rate;
            }
        }
    }

    public function convert_coupon_amount( $amount, $coupon ) {
        if ( ! $coupon instanceof WC_Coupon ) {
            return $amount;
        }

        if ( ! in_array( $coupon->get_discount_type(), [ 'fixed_cart', 'fixed_product' ], true ) ) {
            return $amount;
        }

        return $this->convert_numeric( $amount );
    }

    public function convert_plain_amount( $amount, $object ) {
        unset( $object );

        return $this->convert_numeric( $amount );
    }

    public function filter_decimal_separator(): string {
        return 'en' === dcui_current_language() ? '.' : ',';
    }

    public function filter_thousand_separator(): string {
        return match ( dcui_current_language() ) {
            'en'    => ',',
            'fr'    => "\u{202F}",
            default => '.',
        };
    }

    public function ajax_switch_currency(): void {
        check_ajax_referer( 'm3_currency_nonce', 'nonce' );

        $currency = isset( $_POST['currency'] ) && is_string( $_POST['currency'] )
            ? strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ) ) )
            : '';

        if ( ! in_array( $currency, self::ALLOWED_CURRENCIES, true ) ) {
            wp_send_json_error( [ 'message' => 'Unsupported currency.' ], 400 );
        }

        $this->set_currency( $currency );
        nocache_headers();

        wp_send_json_success(
            [
                'currency' => $currency,
                'rate'     => $this->get_rate(),
                'reload'   => true,
            ]
        );
    }

    public function render_switcher(): string {
        if ( ! $this->site_uses_supported_base_currency() ) {
            return '';
        }

        $current_currency = $this->get_currency();
        $label = dcui_text(
            [
                'de' => 'Währung auswählen',
                'en' => 'Select currency',
                'fr' => 'Sélectionner la devise',
            ]
        );

        ob_start();
        ?>
        <div class="m3-currency-switcher">
            <select aria-label="<?php echo esc_attr( $label ); ?>">
                <option value="CHF" <?php selected( $current_currency, 'CHF' ); ?>>CHF</option>
                <option value="EUR" <?php selected( $current_currency, 'EUR' ); ?>>EUR</option>
            </select>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public function enqueue_assets(): void {
        if ( is_admin() || ! $this->site_uses_supported_base_currency() ) {
            return;
        }

        $currency = $this->get_currency();

        wp_enqueue_style( 'm3-currency-switcher', DCUI_URL . 'assets/css/currency-switcher.css', [], DCUI_VERSION );
        wp_enqueue_script( 'm3-currency-switcher', DCUI_URL . 'assets/js/currency-switcher.js', [ 'jquery' ], DCUI_VERSION, true );
        wp_localize_script(
            'm3-currency-switcher',
            'M3Currency',
            [
                'currency'          => $currency,
                'currencySymbol'    => get_woocommerce_currency_symbol( $currency ),
                'priceFormat'       => get_woocommerce_price_format(),
                'decimalSeparator'  => $this->filter_decimal_separator(),
                'thousandSeparator' => $this->filter_thousand_separator(),
                'decimals'          => 2,
                'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce( 'm3_currency_nonce' ),
                'action'            => self::AJAX_ACTION,
            ]
        );
    }

    public function update_exchange_rate(): void {
        $response = wp_remote_get(
            'https://api.frankfurter.app/latest?from=CHF&to=EUR',
            [
                'timeout'     => 10,
                'redirection' => 2,
                'headers'     => [ 'Accept' => 'application/json' ],
            ]
        );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        $rate = is_array( $data ) && isset( $data['rates']['EUR'] ) && is_numeric( $data['rates']['EUR'] )
            ? (float) $data['rates']['EUR']
            : 0.0;

        // Reject clearly invalid upstream values rather than corrupting all prices.
        if ( $rate < 0.1 || $rate > 10.0 ) {
            return;
        }

        update_option( self::OPTION_KEY, $rate, false );
    }
}

new M3_Currency_Switcher();
