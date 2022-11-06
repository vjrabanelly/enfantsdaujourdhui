"use strict";

jQuery(document).ready(function($){
    /*
	* jQuery UI sortable
	*/
			
	$( "#dynamic-meta-content" ).sortable({
		revert: true,
		stop: function( event, ui ) {
			ui.item.find('textarea.tinymce').each(function(index, elem){
				var $elem = $(elem);
				if($elem.hasClass('tinymce')){
					if(tinymce.majorVersion < 4){
						 tinymce.execCommand('mceRemoveControl', false, $elem.attr('id'));
					}else{
						tinymce.get($elem.attr('id')).destroy();					
					}										
					startTinyMCE('#' + $elem.attr('id'));
				}else{
				
				}
			});
		}
	}).sortable('disable');
	
	$('#dynamic-meta-wrapper').on('mouseenter', '.dynamic-meta-box-title', function(e){
		$( "#dynamic-meta-content" ).sortable('enable');
	});
	
	
	$('#dynamic-meta-wrapper').on('mouseleave', '.dynamic-meta-box-title', function(e){
		$( "#dynamic-meta-content" ).sortable('disable');
	});
	
	$('#dynamic-meta-wrapper').on('mouseleave', '.dynamic-meta-box', function(e){
		$(this).find('.wp-picker-open').removeClass('wp-picker-open');
		$(this).find('.wp-color-picker').css('display', 'none');
	});
	    
    $('.numeric-updown').numericUpDown();
	
	$('#dynamic-meta-wrapper').on('click', '.dm-size-up', function(e){
		var box = $(this).closest('.dynamic-meta-box').get(0);
		var size = $(box).find('.dm-size');
		var sval = parseInt($(size).val());
        var max = parseInt($(size).data('max'));
        var min = parseInt($(size).data('min'));
        var step = parseInt($(size).data('step'));
		if(sval < max){
			sval += step;
            if(sval > max){
                sval = max;
            }
			$(box).css('width', sval + '%');
			$(size).val(sval);
		}else{            
			sval = max;
			$(box).css('width', sval + '%');
			$(size).val(sval);
        }
		$('#dynamic-meta-content').trigger('boxresize', [box]);
	});
    
	$('#dynamic-meta-wrapper').on('click', '.dm-size-down', function(e){
		var box = $(this).closest('.dynamic-meta-box').get(0);
		var size = $(box).find('.dm-size');
		var sval = parseInt($(size).val());
        var max = parseInt($(size).data('max'));
        var min = parseInt($(size).data('min'));
        var step = parseInt($(size).data('step'));
        
		if(sval > min){
			sval -= step;
            if(sval < min){
                sval = min;
            }
			$(box).css('width', sval + '%');
			$(size).val(sval);
		}else{
			sval = min;
			$(box).css('width', sval + '%');
			$(size).val(sval);
		}
		$('#dynamic-meta-content').trigger('boxresize', [box]);
	});	
    	
	$('#dynamic-meta-add').click(function(e){		
		var data = {
			action: 'pukka_get_dm_box',
			type: $('#dynamic-meta-select').val()
		}
		$('#dynamic-meta-loading').css('display', 'inline');
		$.post(ajaxurl, data, function(res){
			$('#dynamic-meta-loading').css('display', '');
			$('#dynamic-meta-content').append(res);
            $('#dynamic-meta-content').trigger('dmadded');
		});		
	});
    
	
	var menuClicked = 'top';
    $('.dm-toolbar li').click(function(e){		
		var data = {
			action: 'pukka_get_dm_box',
			type: $(this).data('type')
		}
		if($(this).parent().hasClass('top')){
			menuClicked = 'top';
		}else{
			menuClicked = 'bottom';
		}
		$(this).find('.dm-tool-loading').css('display', 'block');
		$.post(ajaxurl, data, function(res){
			$('#dynamic-meta-loading').css('display', '');
			if('top' == menuClicked){
				$('#dynamic-meta-content').prepend(res);
			}else{
				$('#dynamic-meta-content').append(res);
			}
            $('#dynamic-meta-content').trigger('dmadded');
            $('.dm-tool-loading').css('display', '');      
		});  		
	});
    
    $('#dynamic-meta-wrapper').on('click', '.dm-edit', function(e){
		$(this).parent().parent().children('.dm-content-wrap').children('.open-editor').click();
	});	
	    
    $('#dynamic-meta-content').on('focus', 'textarea', function(e){
        $(this).closest('.dm-content-box').addClass('focus');
    });
           
    $('#dynamic-meta-content').on('blur', 'textarea', function(e){
        $(this).closest('.dm-content-box').removeClass('focus');
    });
	
	//color-picker color reset	
	$('#dynamic-meta-wrapper').on('click', '.dynamic-meta-box .dm-colors-reset', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).find('.dm-select-color, .dm-edit, .dm-color').val('').change();
	});
	
	setTimeout(function(){$('#dynamic-meta-content').trigger('dmadded')}, 500);
	
	/*
	* TinyMCE and stuff
	*/
	var UID = 1;
	$('#dynamic-meta-wrapper textarea').each(function(index, elem){
		elem.id = 'dm_mce_' + UID;
		UID++;
	});
	function prepareTextAreas(){
		startTinyMCE("textarea.new-tinymce");
		$('#dynamic-meta-wrapper textarea.new-tinymce').addClass('tinymce').removeClass('new-tinymce');
	}
	
	$('#dynamic-meta-wrapper textarea').addClass('new-tinymce');
	setTimeout(prepareTextAreas, 500);
	
	// when new dm added, init tinymce on that element
	$('#dynamic-meta-content').on('dmadded dmmcerefresh', function(e){
		$('#dynamic-meta-wrapper textarea').each(function(index, elem){
			var $elem = $(elem);
			if(typeof(elem.id) == 'undefined' || elem.id == ''){
				elem.id = 'dm_mce_' + UID;
				UID++;
			}
			if(!$elem.hasClass('tinymce') && !$elem.hasClass('mce-off')){
				$elem.addClass('new-tinymce');
			}
		});
		prepareTextAreas();
	});
	
	
	// TODO: srediti ovo da ne radi ovako
	setInterval(function(e){
		$('textarea.tinymce').each(function(index, elem){
			var $elem = $(elem);
			if(null != tinymce.get($elem.attr('id'))){
				tinymce.get($elem.attr('id')).save();
			}
			$elem.trigger('blur');
		});
	}, 2000);
	
	$('#dynamic-meta-content').on('click', '.dm-enable-mce', function(e){
		var $txtBox = $(this).closest('.dm-content-box').find('textarea.tinymce, textarea.mce-off');
		var $check = $(this);
		
		$txtBox.each(function(index, elem){
			var $elem = $(elem);
			if($check.is(':checked')){
				startTinyMCE('#' + $elem.attr('id'));
				$elem.removeClass('mce-off').addClass('tinymce');
			}else{
				if(tinymce.majorVersion < 4){
					 tinymce.execCommand('mceRemoveControl', false, $elem.attr('id'));
				}else{
					tinymce.get($elem.attr('id')).destroy();					
				}
				$elem.removeClass('tinymce').addClass('mce-off');
			}
		});
	});
		
	/*
	$('.dynamic-meta-box').blur(function(e){
		$(this).find('textarea.tinymce').each(function(index, elem){
			var $elem = $(elem);
			tinymce.get($elem.attr('id')).save();
			$elem.trigger('blur');
		});
	});
	*/
	
});

function startTinyMCE(selector){
	if(tinymce.majorVersion < 4){
		jQuery(selector).each(function(index, elem){
			tinymce.execCommand('mceAddControl',false, elem.id);
		});		
	}else{
		tinymce.init({
			selector: selector,
			plugins: [
					"image charmap hr",
					"fullscreen media",
					"directionality textcolor paste textcolor"
			],

			toolbar1: "fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontsizeselect",
			toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | inserttime preview | forecolor backcolor",
			toolbar3: "hr removeformat | subscript superscript | charmap emoticons | fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",
			
			image_advtab: true,
			menubar: false,
			toolbar_items_size: 'small',

			style_formats: [
					{title: 'Bold text', inline: 'b'},
					{title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
					{title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
					{title: 'Example 1', inline: 'span', classes: 'example1'},
					{title: 'Example 2', inline: 'span', classes: 'example2'},
					{title: 'Table styles'},
					{title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
			],

			templates: [
					{title: 'Test template 1', content: 'Test 1'},
					{title: 'Test template 2', content: 'Test 2'}
			]
		});
	}
}

function updateMCE(item){
	var $item = jQuery(item);
	$item.find('textarea.tinymce').each(function(index, elem){
		var $elem = jQuery(elem);
		tinymce.get($elem.attr('id')).destroy();
		startTinyMCE('#' + $elem.attr('id'));
	});
}