(function($){
    function formatPrice(amount) {
        var parts = Number(amount).toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return parts.join(',');
    }

    function wrapPriceHtml(symbol, displayPrice) {
        return '<bdi><span class="woocommerce-Price-currencySymbol">' + symbol + '</span>&nbsp;' + formatPrice(displayPrice) + '</bdi>';
    }

    function updatePrices(currency, rate) {
        var symbol = currency === 'EUR' ? '€' : 'CHF';

        $('.m3-price').each(function(){
            var $price = $(this);
            var basePrice = parseFloat($price.attr('data-base-price'));
            if (isNaN(basePrice)) {
                return;
            }
            var displayPrice = currency === 'EUR' ? (basePrice * rate) : basePrice;
            $price.html(wrapPriceHtml(symbol, displayPrice));
        });
    }

    function refreshWooAreas() {
        $(document.body).trigger('wc_fragment_refresh');
        $(document.body).trigger('update_checkout');
        $(document.body).trigger('updated_wc_div');
        $(document.body).trigger('updated_cart_totals');

        setTimeout(function() {
            updatePrices(window.M3Currency.currency, window.M3Currency.rate);
            $(document).trigger('m3_prices_recheck');
        }, 300);
    }

    $(function(){
        if (!window.M3Currency) {
            return;
        }

        var $select = $('.m3-currency-switcher select');
        $select.val(window.M3Currency.currency);

        $select.on('mousedown', function(){ $(this).addClass('is-open'); });
        $select.on('blur change', function(){ $(this).removeClass('is-open'); });

        updatePrices(window.M3Currency.currency, window.M3Currency.rate);

        $select.on('change', function(){
            var newCurrency = this.value;
            $.post(window.M3Currency.ajaxUrl, {
                action: window.M3Currency.action,
                currency: newCurrency,
                nonce: window.M3Currency.nonce
            }).done(function(response){
                if (response && response.success) {
                    window.M3Currency.currency = newCurrency;
                    if (response.data && response.data.rate) {
                        window.M3Currency.rate = parseFloat(response.data.rate);
                    }
                    updatePrices(newCurrency, window.M3Currency.rate);
                    $(document).trigger('m3_currency_changed', [newCurrency, window.M3Currency.rate]);
                    refreshWooAreas();
                }
            });
        });

        $(document).on('updated_wc_div updated_cart_totals updated_checkout added_to_cart removed_from_cart wc_fragments_loaded wc_fragments_refreshed m3_prices_recheck', function(){
            updatePrices(window.M3Currency.currency, window.M3Currency.rate);
        });
    });
})(jQuery);
