function ibltutoringchatEdit(runtime, element) {
    $('.save-button', element).bind('click', function() {
        var data = {
	    'form_text':$('#edit_form_id').val(),
	    'required_price':$('#required_price').val(),
	    'wait_time':$('#wait_time').val(),
        };
        var handlerUrl = runtime.handlerUrl(element, 'studio_save');
        $.post(handlerUrl, JSON.stringify(data)).complete(function() {
            window.location.reload(false);
        });
    });

    $('.cancel-button', element).bind('click', function() {
        runtime.notify('cancel', {});
    });
}
