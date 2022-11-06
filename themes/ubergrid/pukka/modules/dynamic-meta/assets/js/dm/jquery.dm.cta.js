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
    
    var colorInput = null;
    var dataBox = null
 
    $('.my-color-field').wpColorPicker(myOptions);
        
    $('#dynamic-meta-content').on('click', '.dm-type-cta .dm-edit', function(e){
        dataBox = $(this).closest('.dm-type-cta');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);            
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    $('#dynamic-meta-content').on('click', '.dm-type-cta .dm-select-color', function(e){
        dataBox = $(this).closest('.dm-type-cta');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);            
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
        
	$('#dynamic-meta-wrapper').on('click', '.dm-type-cta .dm-remove', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).remove();
	});	
	
	$('#dynamic-meta-wrapper').on('change', '.dm-type-cta .dm-data-input', function(e){
		dataBox = $(this).closest('.dm-type-cta');
        collectData();
    });
    
    function collectData(){
        var content = getContentJSON(dataBox);        
        var data = {};
        var inputs = $(dataBox).find('.dm-data-input').get();
        for(var i = 0; i < inputs.length; i++){
            data[$(inputs[i]).data('var')] = $(inputs[i]).val();
        }
        
        content.data[0] = data;
        setContentJSON(dataBox, content);
        
    }
    
    function getContentJSON(dataBox){
		var contentInput = $(dataBox).find('.dm-content').get(0);
        var json = $(contentInput).val();
        if($.trim(json) == ''){
            json = '{"data":[], "num": 0}';
        }
        var content = JSON.parse(json); 
		
		return content;
	}
	
	function setContentJSON(dataBox, content){
		var contentInput = $(dataBox).find('.dm-content').get();
		$(contentInput).val(JSON.stringify(content));
	}
	
});