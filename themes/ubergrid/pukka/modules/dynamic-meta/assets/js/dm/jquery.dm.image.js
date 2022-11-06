"use strict";

jQuery(document).ready(function($){
	var myOptions = {
        defaultColor: false,
        change: function(event, ui){
            $(event.target).val(ui.color.toCSS());
            collectData();
        },
        clear: function() {},
        hide: true,
        palettes: true
    };
	// Uploading files
	var file_frame;
	var id = 1;
	var wp_media_post_id = wp.media.model.settings.post.id;
	var currentBox = null;

	$('#dynamic-meta-content').on('click', '.dm-type-image .dm-edit', function(e){
        currentBox = $(this).closest('.dm-type-image');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });

    $('#dynamic-meta-content').on('click', '.dm-type-image .dm-select-color', function(e){
        currentBox = $(this).closest('.dm-type-image');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });

	$('#dynamic-meta-wrapper').on('click', '.dm-add-image, .dm-image-preview', function(event){
		event.preventDefault();
		currentBox = $(this).closest('.dynamic-meta-box');
		if (file_frame){
		  file_frame.open();
		  return;
		}

		file_frame = wp.media.frames.file_frame = wp.media({
		  title: 'Upload image',
		  button: {
			text: 'Select image'
		  },
		  multiple: false  // Set to true to allow multiple files to be selected
		});

		file_frame.on( 'select', function() {
			var attachment = file_frame.state().get('selection').first().toJSON();
			$(currentBox).find('.dm-image-id').val(attachment.id);
			$(currentBox).find('.dm-image-url').val(attachment.url);
			$(currentBox).find('.dm-image-preview').attr('src', attachment.url);
			wp.media.model.settings.post.id = wp_media_post_id;
			collectData();
		});

		// Finally, open the modal
		file_frame.open();
	});

	$('#dynamic-meta-wrapper').on('click', '.dm-remove-image', function(event){
		currentBox = $(this).closest('.dynamic-meta-box');
		$(currentBox).find('.dm-content').val('');
		$(currentBox).find('.dm-image-id').val('');
		$(currentBox).find('.dm-image-url').val('');
		$(currentBox).find('.dm-image-preview').attr('src', $(currentBox).find('.dm-image-preview').data('default'));
	});

	$('#dynamic-meta-wrapper').on('change', '.image-size-select', function(e){
		currentBox = $(this).closest('.dynamic-meta-box');
		var content = getContentJSON(currentBox);
		if(content.data.length > 0){
			$(currentBox).find('.dm-tool-loading').css('display', 'block');
			var imgId = content.data[0].image_id;
			var imgSize = $(this).val();

			$.post(ajaxurl, {action: 'pukka_get_image_url', img_id: imgId, img_size: imgSize}, function(res){
				res = JSON.parse(res);
				if(res.error == false){
					$(currentBox).find('.dm-image-preview').attr('src', res.url);
					$(currentBox).find('.dm-tool-loading').css('display', 'none');
				}
			});
		}
		collectData();
	});

	$('#dynamic-meta-wrapper').on('click', '.dm-type-image .dm-remove', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).remove();
	});

	function getContentJSON(dataBox){
        var contentInput = $(dataBox).find('.dm-content');
        var json = $(contentInput).val();
        if($.trim(json) == ''){
            json = '{"data":[], "num": 0}';
        }
        var content = JSON.parse(json);

        return content;
    }

    function setContentJSON(dataBox, content){
        var contentInput = $(dataBox).find('.dm-content');
        $(contentInput).val(JSON.stringify(content));
    }

    function collectData(){
        var content = getContentJSON(currentBox);
        var data = {};
        var inputs = $(currentBox).find('.dm-data-input').get();
        for(var i = 0; i < inputs.length; i++){
            data[$(inputs[i]).data('var')] = $(inputs[i]).val();
        }

        content.data[0] = data;
        setContentJSON(currentBox, content);
    }

});
