jQuery(function ($) {
    const config = window.DCUICategoryGridAdmin || {};
    function setPreview(type, attachment) {
        $('#dc_' + type + '_id').val(attachment.id || '');
        $('#' + type + '_prev').html(
            attachment.url
                ? $('<img>', { src: attachment.url, width: 80, height: 80, alt: '' })
                : $('<span>').text(config.noneText || 'None')
        );
    }

    $(document).on('click', '.dcui-category-image-select', function (event) {
        event.preventDefault();
        const type = String($(this).data('type') || '');
        if (!type) {
            return;
        }

        const frame = wp.media({ multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            setPreview(type, attachment);
        });
        frame.open();
    });

    $(document).on('click', '.dcui-category-image-remove', function (event) {
        event.preventDefault();
        const type = String($(this).data('type') || '');
        if (!type) {
            return;
        }
        setPreview(type, {});
    });
});
