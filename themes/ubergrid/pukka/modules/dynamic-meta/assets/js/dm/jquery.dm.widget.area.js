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

	$('#dynamic-meta-content').on('click', '.dm-type-widget-area .dm-edit', function(e){
        currentBox = $(this).closest('.dm-type-widget-area');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });

    $('#dynamic-meta-content').on('click', '.dm-type-widget-area .dm-select-color', function(e){
        currentBox = $(this).closest('.dm-type-widget-area');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });

	$('#dynamic-meta-wrapper').on('change', '.dm-widget-area-select', function(e){
		currentBox = $(this).closest('.dynamic-meta-box');
		var sidebarId = currentBox.find('.dm-widget-area-select').val();
				
		if(sidebarId != ''){
			currentBox.find('.widget-preview').append('<div class="dm-tool-loading" style="display: block;"></div>');
			
			$.post(ajaxurl, {action: 'pukka_get_dynamic_sidebar', sidebar: sidebarId}, function(res){
				$(currentBox).find('.dm-tool-loading').css('display', 'none');
				currentBox.find('.widget-preview').html(res);
				currentBox.find('.widget-preview').removeClass().addClass('widget-preview ' + sidebarId);
			});
		}else{
			currentBox.find('.widget-preview').html('');
		}
				
		collectData();
	});
	
	
    $('#dynamic-meta-wrapper').on('blur change', '.dm-type-widget-area .dm-data-input', function(e){
		currentBox = $(this).closest('.dynamic-meta-box');
        collectData();
    });
		

	$('#dynamic-meta-wrapper').on('click', '.dm-type-widget-area .dm-remove', function(e){
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
		
		var classes = $(currentBox).find('.dm-custom-classes').val();
		$(currentBox).find('.dm-content-box').removeClass().addClass('dm-content-box ' + classes); 
    }
	
});
