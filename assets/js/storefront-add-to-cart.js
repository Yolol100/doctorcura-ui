jQuery(function ($) {
    const $containers = $('.master-variation-container');
    const $forms = $('.variations_form');
    const $allPriceDisplays = $('.gs-price-main, .gs-price-sticky');
    const $priceRows = $('.price-row-wrapper');
    const $stickyBar = $('#googleStickyBar');
    let variationSelected = false;
    const config = window.DCUIAddToCart || {};
    const saveTextTemplate = config.saveTextTemplate || 'Save %s%';

    const setScrollPadding = () => {
        const barHeight = $stickyBar.outerHeight() || 0;
        document.documentElement.style.scrollPaddingBottom = (barHeight + 24) + 'px';
        document.body.style.scrollPaddingBottom = (barHeight + 24) + 'px';
    };

    const fmt = (number) => {
        const currency = window.M3Currency || {};
        const decimals = Number.isFinite(Number(currency.decimals)) ? Math.max(0, parseInt(currency.decimals, 10)) : 2;
        const decimalSeparator = currency.decimalSeparator || ',';
        const thousandSeparator = currency.thousandSeparator || '.';
        const symbol = currency.currencySymbol || (currency.currency === 'EUR' ? '€' : 'CHF');
        const priceFormat = currency.priceFormat || '%1$s %2$s';
        const parts = Number(number || 0).toFixed(decimals).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
        const formattedNumber = parts.join(decimalSeparator);

        return priceFormat.replace('%1$s', symbol).replace('%2$s', formattedNumber);
    };

    const formatSaveText = (save) => saveTextTemplate.replace('%s', String(save));

    const renderVariationPrice = (variation) => {
        if (!variation || typeof variation.display_price === 'undefined') return;
        const current = parseFloat(variation.display_price);
        const regular = parseFloat(variation.display_regular_price);
        let html = '';

        if (regular > current && Math.abs(regular - current) > 0.01) {
            const save = Math.round(((regular - current) / regular) * 100);
            html = `<span class="regular-price-strike">${fmt(regular)}</span><span class="amount">${fmt(current)}</span><span class="price-savings-percent">(${formatSaveText(save)})</span>`;
        } else {
            html = `<span class="amount">${fmt(current)}</span>`;
        }

        $priceRows.html(html);
        $allPriceDisplays.addClass('show');
        variationSelected = true;
        toggleStickyVisibility();
    };

    const rerenderCurrentVariationPrice = () => {
        $forms.each(function () {
            const $form = $(this);
            const variation = $form.data('current_variation') || $form.find('input.variation_id').data('variation');
            if (variation && variation.variation_id) {
                renderVariationPrice(variation);
            }
        });
    };

    const toggleStickyVisibility = () => {
        const scrollPos = $(window).scrollTop();
        const width = $(window).width();
        const threshold = width <= 768 ? 400 : 250;
        if (scrollPos <= threshold) {
            $stickyBar.removeClass('is-visible').attr('aria-hidden', 'true').attr('inert', '');
            return;
        }
        if (width > 768 || variationSelected) {
            $stickyBar.addClass('is-visible').attr('aria-hidden', 'false').removeAttr('inert');
        } else {
            $stickyBar.removeClass('is-visible').attr('aria-hidden', 'true').attr('inert', '');
        }
        setScrollPadding();
    };

    const refreshSwatches = () => {
        $containers.each(function () {
            const $cont = $(this);
            $cont.find('select').each(function () {
                const $select = $(this);
                const $wrapper = $select.next('.google-swatches-wrapper');
                if (!$wrapper.length) return;
                const currentVal = $select.val();
                $wrapper.find('.swatch-item').each(function () {
                    const $item = $(this);
                    const val = $item.attr('data-value');
                    const $opt = $select.find(`option[value="${val}"]`);
                    const selected = val === currentVal;
                    const disabled = !selected && ($opt.length === 0 || $opt.is(':disabled'));
                    $item.toggleClass('selected', selected);
                    $item.toggleClass('disabled', disabled);
                    $item.prop('disabled', disabled);
                    $item.attr('aria-pressed', selected ? 'true' : 'false');
                    $item.attr('aria-disabled', disabled ? 'true' : 'false');
                });
            });
        });
    };

    $containers.find('select').each(function () {
        const $select = $(this);
        if ($select.hasClass('swatches-processed')) return;
        const label = $select.closest('tr').find('th.label').text().trim() || $select.attr('name') || 'Variation';
        const $wrapper = $('<div/>', { class: 'google-swatches-wrapper', role: 'group', 'aria-label': label });
        $select.find('option').each(function () {
            const val = $(this).val();
            if (!val) return;
            $('<button/>', {
                type: 'button',
                class: 'swatch-item',
                'data-value': val,
                'aria-pressed': 'false',
                text: $(this).text()
            }).appendTo($wrapper);
        });
        $select.after($wrapper).addClass('swatches-processed');
    });

    // .swatch-item is a native button; Enter/Space already dispatch click.
    $(document).on('click', '.swatch-item', function () {
        const $this = $(this);
        if ($this.hasClass('disabled') || $this.is(':disabled')) return;
        const $parentSelect = $this.closest('.google-swatches-wrapper').prev('select');
        const attrName = $parentSelect.attr('name');
        if (!attrName) return;
        const newVal = $this.hasClass('selected') ? '' : $this.attr('data-value');
        $(`select[name="${attrName}"]`).val(newVal).trigger('change');
        refreshSwatches();
    });

    $forms.on('show_variation', function (e, variation) {
        $(this).data('current_variation', variation);
        renderVariationPrice(variation);
    });

    $forms.on('hide_variation reset_data', function () {
        $(this).removeData('current_variation');
        $allPriceDisplays.removeClass('show');
        if ($(window).width() <= 768) variationSelected = false;
        toggleStickyVisibility();
    });

    document.documentElement.classList.add('dcui-js');

    $(window).on('scroll resize', toggleStickyVisibility);
    refreshSwatches();
    toggleStickyVisibility();

    $(document).on('m3_currency_changed', function () {
        rerenderCurrentVariationPrice();
    });
});
