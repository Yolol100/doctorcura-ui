(function ($) {
    'use strict';

    $(function () {
        // Native buttons already map Enter/Space to click; handling keydown too
        // would toggle twice for keyboard users.
        $(document).on('click', '.wa-acc-trigger', function () {
            var $btn = $(this);
            var $row = $btn.closest('.wa-acc-row');
            var $panel = $row.find('.wa-acc-panel');
            var willOpen = !$row.hasClass('is-active');

            if (willOpen) {
                $panel.prop('hidden', false).stop(true, true).hide().slideDown(250);
            } else {
                $panel.stop(true, true).slideUp(250, function () {
                    $panel.prop('hidden', true).removeAttr('style');
                });
            }

            $row.toggleClass('is-active', willOpen);
            $btn.attr('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
})(jQuery);
