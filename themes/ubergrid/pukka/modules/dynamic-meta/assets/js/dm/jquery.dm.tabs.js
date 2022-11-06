"use strict";


jQuery(document).ready(function($){
    var UID = 0;
    var elemEditing = false;
    var elemEditIndex = null;
	
	
    var currentBox = null;
    var colorOptions = {
        defaultColor: false,
        change: function(event, ui){
            var color = ui.color.toCSS();
            if($(event.target).hasClass('dm-edit')){
                $(currentBox).find('.dm-tabs-bg-color').val(color);
            }
            $(event.target).val(ui.color.toCSS());
            
            collectData();
        },
        clear: function() {},
        hide: true,
        palettes: true
    };
    
    $('.tabs-color').wpColorPicker(colorOptions);
    
    $('.dm-type-tabs').each(function(index){
       setSizes(this); 
    });
    
    /*
     *  Activating color-picker for background color
     */
    $('#dynamic-meta-content').on('click', '.dm-type-tabs .dm-edit', function(e){
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(colorOptions);    
            currentBox = $(this).closest('.dm-type-tabs').get(0);
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    /*
     *  Activating color-picker for text color
     */
    $('#dynamic-meta-content').on('click', '.dm-type-tabs .dm-select-color', function(e){
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(colorOptions);    
            currentBox = $(this).closest('.dm-type-tabs').get(0);
        }       
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });
    
    
    
    /*
     * Selecting tab
     */
    
    $('#dynamic-meta-wrapper').on('click', '.tabs-title li', function(e){
       $(this).parent().children('li').removeClass('current');
       $(this).addClass('current');
       var list = $(this).parent().children('li').get();
       var index = $.inArray(this, list);
       var content = $(this).closest('.dynamic-meta-box').find('.tabs-body li').get();
       $(content).css('display', 'none');
       $(content[index]).css('display', 'block');
    });
    
	$('#dynamic-meta-wrapper').on('focus', '.tabs-title li input', function(e){
         $(this).closest('li').click();
     });
	
     $('#dynamic-meta-wrapper').on('blur', '.tabs-title li input, .tabs-body li textarea', function(e){
         currentBox = $(this).closest('.dynamic-meta-box');
         collectData();
     });
    
     /*
     * Removing box
     */
	$('#dynamic-meta-wrapper').on('click', '.dm-type-tabs .dm-remove', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).remove();
	});
    
     /*
     * Removeing tab
     */
	$('#dynamic-meta-wrapper').on('click', '.dm-type-tabs .dm-remove-tab', function(e){        
        var titleLi = $(this).closest('ul').find('li').get();
        if(titleLi.length == 1) return; // if there is only one tab, we don't remove it
        
		if(!confirm('Are you sure you want to remove this item?')){
            return;
        }
        var box = $(this).closest('.dynamic-meta-box');
        //lista sadrzaja tabova
        var contentLi = $(this).closest('.dm-type-tabs').find('.tabs-body li').get();
        var item = $(this).closest('li').get(0);
        // getting the index of the tab we are removing
        var index = $.inArray(item, titleLi);
        
        $(titleLi[index]).remove();
        $(contentLi[index]).remove();
        // when tab is removed, we have to resize other tabs
        currentBox = box;
        setSizes(box);
        if(index == 0){
            index = 1;
        }
        collectData();
        //selecting previus tab
        $(titleLi[index - 1]).click();		
	});
    
    /*
     * Adding new box, arranging tabs
     */
    $('#dynamic-meta-content').on('dmadded', function(e){
        var li = $('#dynamic-meta-wrapper .dynamic-meta-box').get();
        var last = li[li.length-1];        
        if($(last).hasClass('dm-type-tabs')){
            setSizes(last);
        }
    });
    
    /*
     * Adding new tab
     */
    
    $('#dynamic-meta-content').on('click', '.dm-add-tab', function(e){
        // .dynamic-meta-box holds everything
        var box = $(this).closest('.dynamic-meta-box');
        // list of all tabs
        var titleLi = $(this).closest('ul').find('li').get();
        // index of the current tab
        var index = $.inArray($(this).closest('li').get(0), titleLi);
        // create new tab by cloning first
        var newTitle = $(titleLi[0]).clone(false);
        // remove all the content from clone (maybe not needed becase clone was called with 'false')
        $(newTitle).find('input').val('');
        // insert new element after the current
        newTitle = $(titleLi[index]).after(newTitle);
        // list of all the tab content elements
        var bodyLi = $(box).find('.tabs-body li').get();
        // create new content element by cloning
        var newContent = $(bodyLi[0]).clone(false);
        // remove all content from clone
        $(newContent).find('textarea').val('');
        // inserting element in its rightful place
        $(bodyLi[index]).after(newContent);
        // since there is one more tab, we need to resize them all
        setSizes(box);
    });
    
    function setSizes(box){
        var tabsLi = $(box).find('.tabs-title li').get();
        var size = Math.floor(100 / tabsLi.length);
        $(box).find('.tabs-title li').css('width', size + '%');
        $(box).find('.tabs-body').css('width', size*tabsLi.length + '%');
    }
    
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
        var dataWrap = $(currentBox).find('.dm-content-wrap');
        var titleBoxes = $(dataWrap).find('.tabs-title li').get();
        var contentBoxes = $(dataWrap).find('.tabs-body li').get();
        
        var data = new Array();
        for(var i = 0; i < titleBoxes.length; i++){
            var title = $(titleBoxes[i]).find('.dm-data-input');
            var rest = $(contentBoxes[i]).find('.dm-data-input');
            var elem = {};
            for(var j = 0; j < title.length; j++){
                elem[$(title[j]).data('var')] = $(title[j]).val();
            }
            for(var j = 0; j < rest.length; j++){
                elem[$(rest[j]).data('var')] = $(rest[j]).val();
            }
            data.push(elem);
        }
        
        var content = {
            data: data,
            num: data.length
        }
        
        setContentJSON(currentBox, content);
        
    }
   
});
