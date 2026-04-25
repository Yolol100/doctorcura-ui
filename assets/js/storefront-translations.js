(function () {
    const cfg = window.DCUITranslations || {};
    const replacements = cfg.replacements || {};



    const setShippingCheckboxLabel = () => {
        const checkboxSpan = document.querySelector('#ship-to-different-address label span');
        if (!checkboxSpan) {
            return;
        }
        const current = checkboxSpan.textContent ? checkboxSpan.textContent.trim() : '';
        if (!current || current === 'Ship to a different address?' || current === 'Ship to a different address' || current === 'An eine andere Adresse liefern?') {
            checkboxSpan.textContent = 'Lieferadresse anstelle der Rechnungsadresse verwenden';
        }
    };

    const replaceExactText = (root, selector) => {
        root.querySelectorAll(selector).forEach(function (el) {
            const text = el.textContent ? el.textContent.trim().replace(/\s+/g, ' ') : '';
            if (replacements[text]) {
                el.textContent = replacements[text];
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.body;
        if (!root) return;

        replaceExactText(root, '.screen-reader-text');
        replaceExactText(root, '.woocommerce-message');
        replaceExactText(root, '.woocommerce-MyAccount-content-wrapper p');
        replaceExactText(root, '.woocommerce-MyAccount-content p');
        replaceExactText(root, '.woocommerce-checkout-review-order-table th, .shop_table th, .shop_table td > strong');

        document.querySelectorAll('button[value="Reset password"], .woocommerce-Button.button').forEach(function (btn) {
            const text = btn.textContent ? btn.textContent.trim() : '';
            if (replacements[text]) {
                btn.textContent = replacements[text];
            }
            const value = btn.getAttribute('value');
            if (value && replacements[value]) {
                btn.setAttribute('value', replacements[value]);
            }
        });

        document.querySelectorAll('noscript').forEach(function (el) {
            const html = el.innerHTML;
            if (html.indexOf('Update totals') !== -1 && replacements['Update totals']) {
                el.innerHTML = html.replace(/Update totals/g, replacements['Update totals']);
            }
        });

        setShippingCheckboxLabel();

        document.addEventListener('change', function (event) {
            if (event.target && event.target.id === 'ship-to-different-address-checkbox') {
                setShippingCheckboxLabel();
            }
        });

        if (window.jQuery) {
            window.jQuery(document.body).on('updated_checkout country_to_state_changed', function () {
                setShippingCheckboxLabel();
            });
        }

        document.querySelectorAll('.wa-price-prefix').forEach(function (el) {
            var text = el.textContent ? el.textContent.trim() : '';
            if (text === 'Ab' || text === 'ab') {
                el.textContent = 'ab';
                el.setAttribute('lang', 'de');
            }
        });
    });
})();
