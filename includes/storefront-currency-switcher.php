<?php

declare(strict_types=1);

if (!defined('ABSPATH')) { exit; }

final class M3_Currency_Switcher {
    private const COOKIE_NAME        = 'm3_selected_currency';
    private const BASE_CURRENCY      = 'CHF';
    private const TARGET_CURRENCY    = 'EUR';
    private const OPTION_KEY         = 'm3_exchange_rate_chf_eur';
    private const DEFAULT_RATE       = 1.06;
    private const COOKIE_DURATION    = 30 * DAY_IN_SECONDS;
    private const CRON_HOOK          = 'm3_update_currency_rate';
    private const AJAX_ACTION        = 'm3_switch_currency';
    private const ALLOWED_CURRENCIES = [self::BASE_CURRENCY, self::TARGET_CURRENCY];

    public function __construct() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $this->register_cron();
        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_filter('woocommerce_price_num_decimals', fn() => 2);
        add_filter('woocommerce_price_decimal_separator', [$this, 'filter_decimal_separator']);
        add_filter('woocommerce_price_thousand_separator', [$this, 'filter_thousand_separator']);
        add_filter('woocommerce_get_price_html', [$this, 'add_price_data_attributes'], 10, 2);
        add_filter('woocommerce_cart_item_price', [$this, 'filter_cart_item_price_html'], 10, 3);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'filter_cart_item_price_html'], 10, 3);
        add_filter('woocommerce_cart_subtotal', [$this, 'filter_totals_price_html'], 10, 1);
        add_filter('woocommerce_cart_total', [$this, 'filter_totals_price_html'], 10, 1);
        add_filter('woocommerce_get_formatted_order_total', [$this, 'filter_totals_price_html'], 10, 1);
        add_filter('woocommerce_widget_cart_item_quantity', [$this, 'filter_mini_cart_item_quantity'], 10, 3);
        add_filter('woocommerce_cart_item_name', [$this, 'filter_checkout_item_name'], 10, 3);
        add_shortcode('currency_switcher', [$this, 'render_switcher']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'ajax_switch_currency']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'ajax_switch_currency']);
    }

    private function register_cron(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'twicedaily', self::CRON_HOOK);
        }
        add_action(self::CRON_HOOK, [$this, 'update_exchange_rate']);
    }

    public function get_currency(): string {
        $cookie_currency = isset($_COOKIE[self::COOKIE_NAME]) ? sanitize_text_field(wp_unslash((string) $_COOKIE[self::COOKIE_NAME])) : null;
        $session_currency = function_exists('WC') && WC()->session ? WC()->session->get(self::COOKIE_NAME) : null;
        $currency = $cookie_currency ?? $session_currency;

        return in_array($currency, self::ALLOWED_CURRENCIES, true) ? $currency : self::BASE_CURRENCY;
    }

    private function set_currency(string $currency): void {
        if (!in_array($currency, self::ALLOWED_CURRENCIES, true)) {
            $currency = self::BASE_CURRENCY;
        }
        if (function_exists('WC') && WC()->session) {
            WC()->session->set(self::COOKIE_NAME, $currency);
        }
        setcookie(self::COOKIE_NAME, $currency, [
            'expires'  => time() + self::COOKIE_DURATION,
            'path'     => COOKIEPATH ?: '/',
            'domain'   => COOKIE_DOMAIN ?: '',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function get_rate(): float {
        return (float) get_option(self::OPTION_KEY, self::DEFAULT_RATE);
    }

    public function add_price_data_attributes(string $price_html, $product): string {
        if (!$product instanceof WC_Product || strpos($price_html, 'data-base-price') !== false) {
            return $price_html;
        }
        if ($product->is_type('variable')) {
            $variation_prices = $product->get_variation_prices(true);
            if (empty($variation_prices['price'])) {
                return $price_html;
            }
            $min = min($variation_prices['price']);
            $max = max($variation_prices['price']);
            $replaced = 0;
            return preg_replace_callback('/class="([^"]*woocommerce-Price-amount[^"]*)"/', function ($matches) use (&$replaced, $min, $max) {
                if ($replaced >= 2) {
                    return $matches[0];
                }
                $price_to_use = ($replaced === 0) ? $min : $max;
                $replaced++;
                return 'class="' . $matches[1] . ' m3-price" data-base-price="' . esc_attr($price_to_use) . '"';
            }, $price_html);
        }
        $base_price = (float) $product->get_price('edit');
        return ($base_price > 0) ? $this->wrap_with_data_attr($price_html, $base_price) : $price_html;
    }

    public function filter_cart_item_price_html($price_html, $cart_item, $cart_item_key) {
        $price = (float) $cart_item['data']->get_price();
        if (current_filter() === 'woocommerce_cart_item_subtotal') {
            $price *= (int) $cart_item['quantity'];
        }
        return $this->wrap_with_data_attr($price_html, $price);
    }

    public function filter_totals_price_html($price_html) {
        if (!function_exists('WC') || !WC()->cart) {
            return $price_html;
        }
        $total = (current_filter() === 'woocommerce_cart_subtotal') ? (float) WC()->cart->get_subtotal() : (float) WC()->cart->get_total('edit');
        return $this->wrap_with_data_attr($price_html, $total);
    }

    public function filter_mini_cart_item_quantity($html, $cart_item, $cart_item_key) {
        $line_total = (float) $cart_item['data']->get_price() * (int) $cart_item['quantity'];
        return $this->wrap_with_data_attr($html, $line_total);
    }

    public function filter_checkout_item_name($product_name, $cart_item, $cart_item_key) {
        if (!is_checkout() || strpos($product_name, 'data-base-price') !== false) {
            return $product_name;
        }

        $line_total = (float) $cart_item['data']->get_price() * (int) $cart_item['quantity'];
        $price_html = wc_price($line_total);
        $wrapped = $this->wrap_with_data_attr($price_html, $line_total);

        if (strpos($product_name, 'class="product-total"') !== false) {
            return preg_replace('/<strong class="product-total">.*?<\/strong>/', '<strong class="product-total">' . $wrapped . '</strong>', $product_name, 1);
        }

        return $product_name . ' <strong class="product-total">' . $wrapped . '</strong>';
    }

    private function wrap_with_data_attr($html, $price) {
        if (strpos($html, 'data-base-price') !== false) {
            return $html;
        }

        $wrapped = preg_replace('/class="([^"]*woocommerce-Price-amount[^"]*)"/', 'class="$1 m3-price" data-base-price="' . esc_attr($price) . '"', $html, 1);
        if ($wrapped !== null && $wrapped !== $html) {
            return $wrapped;
        }

        $wrapped = preg_replace('/class="([^"]*amount[^"]*)"/', 'class="$1 m3-price" data-base-price="' . esc_attr($price) . '"', $html, 1);
        if ($wrapped !== null && $wrapped !== $html) {
            return $wrapped;
        }

        return '<span class="m3-price" data-base-price="' . esc_attr($price) . '">' . $html . '</span>';
    }

    public function filter_decimal_separator(): string {
        return 'en' === dcui_current_language() ? '.' : ',';
    }

    public function filter_thousand_separator(): string {
        return match (dcui_current_language()) {
            'en' => ',',
            'fr' => "\u{202F}",
            default => '.',
        };
    }

    public function ajax_switch_currency(): void {
        check_ajax_referer('m3_currency_nonce', 'nonce');
        $currency = isset($_POST['currency']) ? sanitize_text_field(wp_unslash((string) $_POST['currency'])) : '';
        if (!in_array($currency, self::ALLOWED_CURRENCIES, true)) {
            wp_send_json_error();
        }
        $this->set_currency($currency);
        wp_send_json_success(['currency' => $currency, 'rate' => $this->get_rate()]);
    }

    public function render_switcher(): string {
        $current_currency = $this->get_currency();
        $label = dcui_text([
            'de' => 'Währung auswählen',
            'en' => 'Select currency',
            'fr' => 'Sélectionner la devise',
        ]);
        ob_start(); ?>
        <div class="m3-currency-switcher">
            <select aria-label="<?php echo esc_attr($label); ?>">
                <option value="CHF" <?php selected($current_currency, 'CHF'); ?>>CHF</option>
                <option value="EUR" <?php selected($current_currency, 'EUR'); ?>>EUR</option>
            </select>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public function enqueue_assets(): void {
        wp_enqueue_style('m3-currency-switcher', DCUI_URL . 'assets/css/currency-switcher.css', [], DCUI_VERSION);
        wp_enqueue_script('m3-currency-switcher', DCUI_URL . 'assets/js/currency-switcher.js', ['jquery'], DCUI_VERSION, true);
        wp_localize_script('m3-currency-switcher', 'M3Currency', [
            'rate'              => $this->get_rate(),
            'currency'          => $this->get_currency(),
            'decimalSeparator'  => $this->filter_decimal_separator(),
            'thousandSeparator' => $this->filter_thousand_separator(),
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce('m3_currency_nonce'),
            'action'            => self::AJAX_ACTION,
        ]);
    }

    public function update_exchange_rate(): void {
        $response = wp_remote_get('https://api.frankfurter.app/latest?from=CHF&to=EUR', ['timeout' => 10]);
        if (is_wp_error($response)) {
            return;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['rates']['EUR'])) {
            update_option(self::OPTION_KEY, (float) $data['rates']['EUR'], false);
        }
    }
}

new M3_Currency_Switcher();
