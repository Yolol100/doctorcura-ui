jQuery(function($){
    $('.dc_u').off('click').on('click', function(event){
        event.preventDefault();
        var type = $(this).data('t');
        var frame = wp.media({ multiple: false });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#dc_' + type + '_id').val(attachment.id);
            $('#' + type + '_prev').html('<img src="' + attachment.url + '" width="80" alt="">');
        });
        frame.open();
    });
});
