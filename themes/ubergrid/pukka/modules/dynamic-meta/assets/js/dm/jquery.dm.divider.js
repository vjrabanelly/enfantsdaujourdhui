"use strict";


jQuery(document).ready(function($){
    var dividerBox = null;
    
	var colorOptions = {
            defaultColor: false,
            change: function(event, ui){
                $(event.target).val(ui.color.toCSS());
                collectData();
            },
            clear: function() {},
            hide: true,
            palettes: true
        };
    
   
    /*
     *  Activating color-picker for background color
     */
    $('#dynamic-meta-content').on('click', '.dm-type-divider .dm-edit', function(e){
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(colorOptions);    
            dividerBox = $(this).closest('.dm-type-divider').get();
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    /*
     * Removing divider
     */
    $('#dynamic-meta-wrapper').on('click', '.dm-type-divider .dm-remove', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).remove();
	});	
	
	$('#dynamic-meta-wrapper').on('change', '.dm-type-divider .dm-data-input', function(e){
		dividerBox = $(this).closest('.dynamic-meta-box');
        collectData();
    });
    
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
        var contentInput = $(dataBox).find('.dm-content').get(0);
        $(contentInput).val(JSON.stringify(content));
    }
    
   function collectData(){
        var content = getContentJSON(dividerBox);        
        var data = {};
        var inputs = $(dividerBox).find('.dm-data-input');
        for(var i = 0; i < inputs.length; i++){
            data[$(inputs[i]).data('var')] = $(inputs[i]).val();
        }
        
        content.data[0] = data;
        setContentJSON(dividerBox, content);
    }
    
    
});
