(function($){
    $(function(){
        $(document).on('click keydown', '.wa-acc-trigger', function(event){
            if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            if (event.type === 'keydown') {
                event.preventDefault();
            }

            var $btn = $(this);
            var $row = $btn.closest('.wa-acc-row');
            var $panel = $row.find('.wa-acc-panel');
            var willOpen = !$row.hasClass('is-active');

            if (willOpen) {
                $panel.prop('hidden', false).stop(true, true).hide().slideDown(250);
            } else {
                $panel.stop(true, true).slideUp(250, function(){
                    $panel.prop('hidden', true).removeAttr('style');
                });
            }

            $row.toggleClass('is-active', willOpen);
            $btn.attr('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
})(jQuery);
