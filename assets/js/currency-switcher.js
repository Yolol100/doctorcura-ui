(function ($) {
    'use strict';

    $(function () {
        const config = window.M3Currency || {};
        const $switcher = $('.m3-currency-switcher select');

        if (!$switcher.length || !config.ajaxUrl || !config.nonce || !config.action) {
            return;
        }

        $switcher.on('change', function () {
            const $select = $(this);
            const currency = String($select.val() || '').toUpperCase();

            if (!['CHF', 'EUR'].includes(currency)) {
                return;
            }

            $select.prop('disabled', true).attr('aria-busy', 'true');

            $.post(config.ajaxUrl, {
                action: config.action,
                nonce: config.nonce,
                currency: currency
            })
                .done(function (response) {
                    if (response && response.success) {
                        window.location.reload();
                        return;
                    }
                    $select.prop('disabled', false).removeAttr('aria-busy');
                })
                .fail(function () {
                    $select.prop('disabled', false).removeAttr('aria-busy');
                });
        });
    });
})(jQuery);
