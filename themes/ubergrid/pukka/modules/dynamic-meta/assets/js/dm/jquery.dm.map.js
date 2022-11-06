"use strict";


jQuery(document).ready(function($){
    var dataBox = null;

    var maps = new Array();
    var marker;
    var lat_field;
    var lnt_field;
    var show_start_marker = false;
    var zoomLevel = 3;
    var markers = new Array();

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
    lat_field = '-0.61388867740823';
    lnt_field = '73.14456939697266';


    $('.dm-type-map').each(function(index){
        initializeMap(this);
    });

    $('#dynamic-meta-wrapper').on('mouseenter', '.dm-type-map', function(e){
       dataBox = this;
    });

    $('#dynamic-meta-content').on('click', '.dm-type-map .dm-edit', function(e){
        dataBox = $(this).closest('.dm-type-map');
        if(!$(this).hasClass('wp-color-picker')){
            $(this).wpColorPicker(myOptions);
        }
        $(this).closest('.wp-picker-container').find('.wp-color-result').click();
    });

    /*
     * Brisanje box-a
     */
	$('#dynamic-meta-wrapper').on('click', '.dm-type-map .dm-remove', function(e){
		var box = $(this).closest('.dynamic-meta-box');
		$(box).remove();
	});

    /*
     * Disejblovanje sortiranja elemenata (da bi radio drag na mapi)
     */

    $('#dynamic-meta-wrapper').on('mouseenter', '.dm-type-map .dm-map-container', function(e){
        $('#dynamic-meta-content').sortable('disable');
    });

    $('#dynamic-meta-wrapper').on('mouseleave', '.dm-type-map .dm-map-container', function(e){
        $('#dynamic-meta-content').sortable('enable');
    });

    /*
     * cuvanje vrednosti
     */

    $('#dynamic-meta-wrapper').on('change', '.dm-type-map .dm-data-input', function(e){
        collectData();
    });

    $('#dynamic-meta-wrapper').on('dmadded', function(e){
        var li = $('#dynamic-meta-wrapper .dynamic-meta-box').get();
        var last = li[li.length-1];
        if($(last).hasClass('dm-type-map')){
            dataBox = last;
            $(last).find('.numeric-updown').numericUpDown();
            initializeMap(last);
        }
    });

	$('#dynamic-meta-wrapper').on('click', '.map-box-hide', function(e){
		var parent = $(this).closest('.dynamic-meta-box');
		if($(parent).find('.wrap-hide').css('display') == 'none'){
			$(parent).find('.wrap-hide').slideDown(300);
			$(this).html('&#x25B2;');
		}else{
			$(parent).find('.wrap-hide').slideUp(300);
			$(this).html('&#x25BC;');
		}
	});

	$('#dynamic-meta-wrapper').on('change', '.dm-map-desc-height', function(e){
		var h = $(this).val();
		var box = $(this).closest('.dynamic-meta-box').get(0);
		$(box).find('.dm-map-container').css('height', h);
		var list = $('.dm-type-map').get();
		var index = $.inArray(box, list);
		if(-1 != index){
			google.maps.event.trigger(maps[index], 'resize');
		}
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
        var content = getContentJSON(dataBox);
        var data = {};
        var inputs = $(dataBox).find('.dm-data-input').get();
        for(var i = 0; i < inputs.length; i++){
            data[$(inputs[i]).data('var')] = $(inputs[i]).val();
        }

        content.data[0] = data;
        setContentJSON(dataBox, content);
    }

    function initializeMap(dataBox) {
        var lat = $(dataBox).find('.dm-map-latitude').val();
        var lnt = $(dataBox).find('.dm-map-longitude').val();
        if('' == lat || '' == lnt){
            lat = lat_field;
            lnt = lnt_field;
        }
        var startLatlng = new google.maps.LatLng(lat, lnt);
        var zoomLevel = parseInt($(dataBox).find('.dm-map-zoom').val());
        var mapOptions = {
			scrollwheel: false,
            zoom: zoomLevel,
            center: startLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var box = $(dataBox).find('.dm-map-container').get(0);
        var map = new google.maps.Map(box, mapOptions);
        maps.push(map);
        $(dataBox).find('.dm-map-latitude').val(lat).change();
        $(dataBox).find('.dm-map-longitude').val(lnt).change();

        var marker = new google.maps.Marker({
            position: startLatlng,
            map: map
        });

        markers.push(marker);
        $(dataBox).attr('data-marker', $.inArray(marker, markers));

        google.maps.event.addListener(map, 'click', function(event) {
            placeMarker(event.latLng);
            $(dataBox).find('.dm-map-latitude').val(event.latLng.lat()).change();
            $(dataBox).find('.dm-map-longitude').val(event.latLng.lng()).change();
        });

        google.maps.event.addListener(map, 'zoom_changed', function(event) {
           zoomLevel = parseInt(this.zoom);
           $(dataBox).find('.dm-map-zoom').val(zoomLevel).change();
        });
    }

    function placeMarker(location) {
        var i = parseInt($(dataBox).data('marker'));
        if ( markers[i] ) {
            markers[i].setPosition(location);
        } else {
            var marker = new google.maps.Marker({
                    position: location,
                    map: maps[i]
                });
            markers.push(marker);
            $(dataBox).attr('data-marker', $.inArray(marker, markers));
        }
    }
	$('#dynamic-meta-content').on('boxresize', function(e, box){
		if($(box).hasClass('dm-type-map')){
			var list = $('.dm-type-map').get();
			var index = $.inArray(box, list);
			if(-1 != index){
				google.maps.event.trigger(maps[index], 'resize');
			}
		}
	});
	$(window).resize(function() {
		for(var i = 0; i < maps.length; i++){
			 google.maps.event.trigger(maps[i], 'resize');
		}
	});
});

