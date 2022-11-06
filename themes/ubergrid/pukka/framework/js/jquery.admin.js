"use strict";

jQuery(document).ready(function($){
	var formAction = false;
	// Save options
	$("#pukka-settings").on("submit", function(e){
		//if action in progress, return
		if(formAction) return false;
		e.preventDefault();
		//check who invoke form submit
		//if it is save button, do the save
		formAction = true;
		var form = $(this);
		$(".pukka-ajax-load").css("display", "inline");
		$.post(ajaxurl, form.serialize(), function(response){

				if( typeof(response.error) != 'undefined' && false == response.error){
					showNotification('Saved, your changes have been!', 'success');
					if( typeof(response.fields) != 'undefined' && response.fields.length > 0){
						setThemeOptions(response.fields);
					}
				}
				else{
					showNotification('Error saving changes!', 'error');
				}

				$(".pukka-ajax-load").css("display", "none");
				formAction = false;
		}, "json");


		return false;
	});

	$('#pukka-reset-settings').click(function(e){
		if(!confirm("Are you sure you want to reset all settings? \n(Strings and images will be preserved)")){
				return false;
			}
		formAction = true;
		$(".pukka-ajax-reset").css("display", "inline");
		$.post(ajaxurl, {action: 'pukka_framework_reset'}, function(response){
			if( typeof(response.error) != 'undefined' && false == response.error){
				setThemeOptions(response.fields);
				showNotification(response.message, 'success');
			}
			else{
				showNotification(response.message, 'error');
			}

			$(".pukka-ajax-reset").css("display", "none");
			formAction = false;
		}, "json");
	});


	// Init tabs
	if( $(".pukka-tabs").length > 0 )
		$(".pukka-tabs").tabs();

/*
	// Initi custom selectboxes
	$(".pukka-single-select").selectbox({
		speed: 400
	});

	// Init custom checkboxes
	$('#dynamic-meta-wrapper input, .pukka-input-wrap input').iCheck({
		checkboxClass: 'icheckbox_polaris',
		radioClass: 'iradio_polaris',
		increaseArea: '-10%' // optional
	});
	
	$('#dynamic-meta-wrapper').on({
		dmadded: function(e){
			$(this).find('select').selectbox({
				speed: 400
			});
		}
	},
	'#dynamic-meta-content'
	);


	$("#featured-form").on({
		added : function(e){
			$(this).find('select:visible').selectbox({speed: 400});
		}
	},
	'#featured');
*/
});

function showNotification(msg, type){
	//type = 'success' || 'error';
	var iconClass ='';
	if('success' == type){
		iconClass = 'success';
	}else{
		iconClass = 'error';
	}
	var $ = jQuery;
	var html = document.createElement('div');
	html.className = 'pukka-notification ' + iconClass;
	$(html).css('opacity', '0');
	html.innerHTML = '<div class="icon '+ iconClass + '"></div><div class="message">' + msg + '</div>';

	$('body').append(html);
	$(html).animate({opacity: 1}, 1000, function(){
		$(this).delay(1000).animate({opacity: 0}, 1000, function(){
			$(this).remove();
		});
	});
}

function setThemeOptions(options){
	var $ = jQuery;

	for(var i = 0; i < options.length; i++){
		var elem = options[i];
		var $elem = $('#' + elem.id);

		if('checkbox' == elem.type){
			if('on' == elem.value){
				$('input[type=checkbox]#' + elem.id).attr('checked', 'checked');
			}else{
				$('input[type=checkbox]#' + elem.id).removeAttr('checked');
			}
		}else{
			$elem.val(elem.value).change();
		}

		if('file' == elem.type && '' != elem.url){
			var $imgWrap = $elem.closest('.pukka-input').find('.pukka-img-wrap');
			$imgWrap.html('<img src="' + elem.url + '" alt="Preview" style="max-width: 200px;" />');
			$imgWrap.removeClass('pukka-file-placeholder');
		}
		if('select' == elem.type && $elem.hasClass('pukka-single-select')){
			$elem.addClass('was-sb');
		}
	}
	/*
	$('.was-sb').selectbox('detach');
	$('.was-sb').selectbox({speed: 400});
	
	$('input').iCheck('update');
	*/
}