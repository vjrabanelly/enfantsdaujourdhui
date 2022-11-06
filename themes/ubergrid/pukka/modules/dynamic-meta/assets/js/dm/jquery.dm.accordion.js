"use strict";

var doom;
var j;

jQuery(document).ready(function($){
    j = $;
    var UID = 0;
    var elemEditing = false;
    var elemEditIndex = null;
	
	
    /****************************************
     * Accordion
     ***************************************/
    var accBox = null;
    var colorOptions = {
        defaultColor: false,
        change: function(event, ui){
            var color = ui.color.toCSS();
            if($(event.target).hasClass('dm-edit')){
                $(currentBox).find('.dm-acc-bg-color').val(color);
            }
            $(event.target).val(ui.color.toCSS());
            
            collectData();
        },
        clear: function() {},
        hide: true,
        palettes: true
    };
    var dt = 300;
    var currentBox = null;
    
    /*
     * Sortable for accordion
     */
    $( ".dm-type-accordion .dm-content-wrap ul" ).sortable({
		revert: true,
        update: function( event, ui ) {
            collectData();
        },
        axis: 'y',
		stop: function( event, ui ) {
			updateMCE(ui.item);
		}
	});
    
    $('#dynamic-meta-content').on('dmadded', function(e){
        var li = $('#dynamic-meta-wrapper .dynamic-meta-box').get();
        var last = li[li.length-1];        
        if($(last).hasClass('dm-type-accordion')){
            $(last).find('.dm-content-wrap ul').sortable({
                revert: true,
                update: function( event, ui ) {
                    collectData();
                },
                axis: 'y',
				stop: function( event, ui ) {
					updateMCE(ui.item);
				}
            });
        }
    });
    /*
     *  Activating color-picker for background color
     */
    $('#dynamic-meta-content').on('click', '.dm-type-accordion .dm-edit', function(e){
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(colorOptions);    
            currentBox = $(this).closest('.dm-type-accordion').find('.dm-meta-accordion-box').get(0);
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    /*
     *   Activating color-picker for text color
     */
    $('#dynamic-meta-content').on('click', '.dm-type-accordion .dm-select-color', function(e){
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(colorOptions);    
            currentBox = $(this).closest('.dm-meta-accordion-box').get(0);
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    
    
    /*
     * dodavanje novog accordion box-a
     */
    $('#dynamic-meta-wrapper').on('click', '.dm-add-accordion', function(e){
        var outHTML = '<li class="dm-meta-accordion-box">' +
                            '<div class="accordion-box-open">&#x25BC;</div>' +
                                '<input type="text" placeholder="Enter title here" class="dm-title dm-data-input" value="" data-var="text_title">' +
                                '<div class="dm-content-box">' +
                                    '<textarea class="dm-input dm-text-content dm-data-input" placeholder="Enter content here" data-var="text_content"></textarea>  '+
                                    '<div class="dm-input-tools">' +
										'<div class="dm-colors-reset" title="Return colors to default values"></div>' +
                                        '<input type="hidden" class="dm-data-input dm-acc-bg-color" value="" data-var="bg_color" />' +
                                        '<input type="button" class="dm-select-color dm-data-input dm-acc-text-color" value="" data-var="text_color" />' +
										'<div class="toggle-mce"><span>Editor ON/OFF</span><input type="checkbox" class="dm-data-input dm-enable-mce" value="mce-enable" title="Enable/Disable Advance Editor" checked/></div>' +
                                    '</div>' +
                            '</div>' +
                            '<div class="dm-add-accordion">+</div>' +
                        '</li>';
        $(this).closest('.dm-meta-accordion-box').after(outHTML);
        $(this).closest('.dm-meta-accordion-box').find('.dm-content-wrap ul').sortable('destroy').sortable({
            revert: true,
            update: function( event, ui ) {
                collectData();
            },
			stop: function( event, ui ) {
				updateMCE(ui.item);
			}
        });
		
		$('#dynamic-meta-content').trigger('dmmcerefresh');
    });
	
    /*
     * open/close accordion box
     */
    $('#dynamic-meta-wrapper').on('click', '.accordion-box-open', function(e){
        var parent = $(this).closest('.dm-meta-accordion-box');
        if($(parent).find('.dm-content-box').css('display') != 'none'){
            $(parent).find('.dm-content-box').slideUp(dt);            
        }else{
            $(parent).find('.dm-content-box').slideDown(dt);            
        }
    });
    
    /*
     * oppening accordion box if title input has focus
     */
    $('#dynamic-meta-wrapper').on('focus', '.dm-title', function(e){
        if($(this).closest('.dm-meta-accordion-box').find('.dm-content-box').css('display') == 'none'){
            $(this).closest('.dm-meta-accordion-box').find('.accordion-box-open').click();
        }
    });
    
    /*
     * moving edit controls
     */    
    $('#dynamic-meta-wrapper').on('mouseover', '.dm-meta-accordion-box, .dm-type-accordion .dynamic-meta-box-title', function(e){
        currentBox = this; // saving current box (needed for color-picker)
        
		// setting the color-picker color of the currently selected accordion box
        var color = $(currentBox).find('.dm-acc-bg-color').val();
        $(this).closest('.dynamic-meta-box').find('.dm-edit').val(color).change();
        
        var offset = $(this).position();
		var top = parseInt(offset.top) + parseInt($(this).css('margin-top'));
        $(this).closest('.dynamic-meta-box').find('.dm-size-controls').css('padding-top', top);
    });
    
    /*
     * Removeing accordion box
     */    
	$('#dynamic-meta-wrapper').on('click', '.dm-type-accordion .dm-remove', function(e){        
        if($(currentBox).hasClass('dynamic-meta-box-title')){
            $(this).closest('.dm-type-accordion').remove();
        }else{
			var acc = $(currentBox).closest('ul').find('li').get();
			if(acc.length > 1){
				$(currentBox).remove();
			}
        }
	});	
    
    /*
     * when some input loses focus, we collect all the data
     */    
    $('#dynamic-meta-wrapper').on('blur', '.dm-type-accordion .dm-title, .dm-type-accordion .dm-text-content', function(e){
        collectData();
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
        var dataWrap = $(currentBox).closest('.dm-content-wrap');
        var boxes = $(dataWrap).find('.dm-meta-accordion-box').get();
        
        var data = new Array();
        for(var i = 0; i < boxes.length; i++){
            var inputs = $(boxes[i]).find('.dm-data-input');
            var elem = {};
            for(var j = 0; j < inputs.length; j++){
                elem[$(inputs[j]).data('var')] = $(inputs[j]).val();
            }
            data.push(elem);
        }
        
        var content = {
            data: data,
            num: data.length
        }
        
        setContentJSON(dataWrap, content);        
    }
	
	
});
