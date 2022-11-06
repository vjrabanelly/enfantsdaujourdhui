/* Featured posts script
*
*/
"use strict";

jQuery(document).ready(function($){
	var language = '';
	if (typeof icl_this_lang !== 'undefined') {
		language = icl_this_lang;
	}
	var featured_post_ids;
	// update post ids
	pukka_update_post_ids();

	// used for sorting elements
	$(".sortable").sortable({ opacity: 0.8, cursor: 'move'});
	

	$("#featured").on({
		click: function(e){
			e.preventDefault();
			var parent_li = $(this).closest("li");

			$(parent_li).hide("fast"); // first hide it
			$(parent_li).remove(); // then remove it

			if( $(parent_li).data("type") == "post" ){
				// update post ids
				pukka_update_post_ids();
			}
		}
	}, ".featured-remove");

	// add wp post (autocomplete)
	$('#featured-add-post').click(function(e){
									e.preventDefault();
									var $input = $(this).find('input');
									if('none' == $input.css('display')){
										$input.show(300).animate({top: '120%'}, 300, function(e){
											$(this).focus();
										});
									}
									return false;
								});

	// bind autocomplete if needed
	$(".featured-controls").on({
		focus: function(e) {
			if ( !$(this).data("autocomplete") ) { // If the autocomplete wasn't called yet:
			// compatible with older than wp 3.6 (tested on 3.5.1)			
			$(this).autocomplete({						
						source: ajaxurl + "?featured_post_ids=" +  featured_post_ids + "&action=pukka_add_featured_post&lang=" + language,
						minLength: 2,
						response: function(event, ui) {
							// ui.content is the array that's about to be sent to the response callback.
							if (ui.content.length === 0) {
								// if no posts found, show this message in notification 
								// area (no notification area for this at the moment)
								//$("#empty-message").text("No results found");
							} else {
								//$("#empty-message").empty();
							}
						},
						select: function( event, ui ) {
							var data = {
								action: 'pukka_get_featured_box',
								type: 'post',
								id: ui.item.ID
							}
							getFeaturedBox(data);							
							$(this).val('').animate({top: '100%'}, 300).hide(300);
							
							return false;
					}
					})._renderItem = function( ul, item ) {
					return $( "<li>" )
						.data( "data-ui-id", item.ID )
						.append( $("<a>" ).text( item.label ) )
						.appendTo( ul );
					};
					
			} // if
		},
		click: function(e){
			e.stopPropagation();
		}
	}, ".featured-add-post-input");
	
	$(document).mouseup(function(e){
		var $container = $('.featured-add-post-input');
		if($container.is(":visible") && !$container.is(e.target)
		&& $container.has(e.target).length === 0)
			{
				$container.stop().animate({top: '100%'}, 300).hide(300);
			}
	});

	// add custom content
	$("#featured-add-custom").click(function(e){
		e.preventDefault();
		var data = {
			action: 'pukka_get_featured_box',
			type: 'custom'
		}
		getFeaturedBox(data);
	});

	// add category
	$("#featured-add-tax").click(function(e){
		e.preventDefault();
		var data = {
			action: 'pukka_get_featured_box',
			type: 'term'
		}
		getFeaturedBox(data);
	});
	
	function getFeaturedBox(data){
		$('.waiting').css('display', 'inline');
		$.post(ajaxurl, data, function(res){
			addFeaturedBox(res);
			pukka_update_post_ids();
		});	
	}	
	
	function addFeaturedBox(html){
		$("#featured").prepend(html).trigger('added');
		$('.waiting').css('display', 'none');
	}

	// save featured posts list
	$("#featured-save").click(function(e){
			e.preventDefault();
			
			$('.waiting').show();
			// custom serialize, gets data from data nad input fields
			var itemsData = new Array();
			var singleItem;
			var items = $("#featured li.box");

			$.each(items, function(index, value){
				singleItem = new Object();
				singleItem.type = $(value).data("type");
				singleItem.size = $(value).find(".box-size").val();
				singleItem.pinned = $(value).find(".box-pin").is(":checked") ? $(value).find(".box-pin").val() : "";

				if( $(value).data("type") == "post" ){
					singleItem.id = typeof $(value).data("id") === "undefined" ? "" : $(value).data("id");
				}
				else if( $(value).data("type") == "custom" ){
					singleItem.content = $(value).find(".featured-custom-content").val();
					singleItem.banner = $(value).find(".box-banner").is(":checked") ? $(value).find(".box-banner").val() : "";
				}
				else if( $(value).data("type") == "term" ){
					singleItem.taxonomy = $(value).data("taxonomy");
					singleItem.term_id = $(value).find(".tax-term").val();
				}

			   if( singleItem.type == "post" && singleItem.id == "" ){ // if post box was added but no post selected
					return true; // returning true is same as continue in a for loop
				}
				else{
					itemsData.push(singleItem);
				}
			});
			itemsData = JSON.stringify(itemsData);

			var data = {
				action: 'pukka_save_featured',
				featured_items: itemsData,
				pukka_nonce: $("#featured-nonce").val()
			}
		   
			$.post(ajaxurl, data, function(response){
				$('.waiting').hide();
				showNotification('Saved, your cahnges have been!', 'success');
			}, "json");
	});
	
	//	pinning and unpinning elements
	//
	$("#featured").on('click', '.featured-pin', function(e){
		$(this).find('input').click();
		if($(this).find('input:checked').length > 0){
			$(this).addClass('pinned');
		}else{
			$(this).removeClass('pinned');
		}
	});
	
	// updating hidden fields with featured post ids
	// 
	function pukka_update_post_ids(){
		var items = $("#featured li");
		var post_ids = "";
		$.each(items, function(index, value){
			if( $(value).data("type") == "post" ){
				post_ids += $(value).data("id") +",";
			}
		});

		if( post_ids != "" ){
			// remove last comma and set var
			featured_post_ids = post_ids.substring(0, post_ids.length - 1);
		}
		else{
			featured_post_ids = "";
		}
	}
	
	
});