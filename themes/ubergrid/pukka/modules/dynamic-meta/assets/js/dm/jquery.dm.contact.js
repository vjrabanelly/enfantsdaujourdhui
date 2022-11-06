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
    
    var dataBox = null;
    
    $('#dynamic-meta-content').on('click', '.dm-type-contact .dm-edit', function(e){
        dataBox = $(this).closest('.dm-type-contact');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);            
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    $('#dynamic-meta-content').on('click', '.dm-type-contact .dm-select-color', function(e){
        dataBox = $(this).closest('.dm-type-contact');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);            
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    /*
     * Removing box
     */
	$('#dynamic-meta-wrapper').on('click', '.dm-type-contact .dm-remove', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).remove();
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
    
    
    
    $('#dynamic-meta-wrapper').on('blur', '.dm-email', function(e){
        var val = $(this).val();
        if(!validateEmail(val)){
            $(this).css('border', '1px solid #aa0000');
        }else{
            $(this).css('border', '');
        }
    });
    
    function validateEmail(email) { 
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    } 
});


