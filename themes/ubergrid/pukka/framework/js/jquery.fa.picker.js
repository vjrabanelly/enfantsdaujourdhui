var pukkaFAPicker;

jQuery(document).ready(function($){
	"use strict";

	pukkaFAPicker = {
		picker: function(field_id){

			 if( $("#pukka-fa-picker").length == 0 ){
	            // add wrapper (which will be used for thickbox)
	            $("body").append("<div id='pukka-fa-picker' style='display:none;'></div>");
	            //create picker structure
	            create_picker_structure();
	            // populate icons
	            populate_picker_icons();
	            // init tabs
	            $(".tabbable").tabs();
	        }

	        // attach 'picker' event
	        $(".tdIcon a").on('click', function(e){
	            e.preventDefault();

	            pukkaFAPicker.iconPicked(field_id, $(this).attr('title'))
	        });

	        tb_show("Choose an icon", "#TB_inline?width=600&height=400&inlineId=pukka-fa-picker");

		},


		iconPicked: function(field_id, icon_name){

			// save icon value
			$("#" + field_id).val(icon_name);

			// show preview
			$("#" + field_id +"-preview").html("<i class='fa "+ icon_name +"'></i>");
            $("#" + field_id + "-preview").removeClass('pukka-file-placeholder');

			// show remove button
			$("#" + field_id + "-remove").show();

			// remove 'picker' event
			$(".tdIcon a").off('click');

			//close thickbox
            tb_remove();
		},

		removeIcon: function(field_id){

			// clear icon value
			$("#" + field_id).val("");

			// clear preview
            $("#" + field_id + "-preview").addClass('pukka-file-placeholder');
			$("#" + field_id + "-preview").empty();

			// hide remove button
			$("#" + field_id + "-remove").hide();
		}
	};


	function create_picker_structure(){


        var html = "<div id='icons-dialog' class='' tabindex='-1' role='dialog' aria-hidden='true'>" +
                        "<div class='modal-body'>" +
                            "<div class='tabbable'>" +
                                "<ul id='tab-titles' class='nav nav-tabs' data-tabs='tabs'>" +
                                    "<li class='active'><a href='#one' data-toggle='tab'>A</a></li>" +
                                    "<li><a href='#two' data-toggle='tab'>B</a></li>" +
                                    "<li><a href='#three' data-toggle='tab'>C</a></li>" +
                                    "<li><a href='#four' data-toggle='tab'>D</a></li>" +
                                    "<li><a href='#five' data-toggle='tab'>E</a></li>" +
                                    "<li><a href='#six' data-toggle='tab'>F</a></li>" +
                                    "<li><a href='#seven' data-toggle='tab'>G</a></li>" +
                                    "<li><a href='#eight' data-toggle='tab'>H</a></li>" +
                                    "<li><a href='#nine' data-toggle='tab'>I</a></li>" +
                                "</ul> <!-- #tab-titles -->" +
                                "<div class='active tab-pane' id='one'></div>"+
                                "<div class='tab-pane' id='two'></div>"+
                                "<div class='tab-pane' id='three'></div>"+
                                "<div class='tab-pane' id='four'></div>"+
                                "<div class='tab-pane' id='five'></div>"+
                                "<div class='tab-pane' id='six'></div>"+
                                "<div class='tab-pane' id='seven'></div>"+
                                "<div class='tab-pane' id='eight'></div>"+
                                "<div class='tab-pane' id='nine'></div>"+
                                "</div> <!-- .tabbable -->"+
                            "</div> <!-- .modal-body -->"+
                        "<div class='modal-footer'>"+
                                "Icons by <a href='http://fortawesome.github.io/Font-Awesome/' target='_blank'>Font Awesome</a>."+
                        "</div> <!-- .modal-footer -->"+
                        "</div> <!-- #icons-dialog -->";

            // populate wrapper
            $("#pukka-fa-picker").html(html);
        }

        // populate icon wrapper
        function populate_picker_icons(){
            var ctThisPanel = 0, sCurrent = "<center><table><tr>", panelnum = 0, ctThisRow = 0, maxPerPanel = 45, maxPerRow = 9;
            var clickscript;
            // var tc = document.getElementById("tabs-content");
            var $tc = $("#pukka-fa-picker .tab-pane");
            //var tt = document.getElementById("tab-titles");
            var tt = $("#tab-titles a").get();

            // set first tab title
            $(tt[0]).html("<i class='fa " + awesomeIconsArray[0].name + "'></i>&nbsp;");
            
            for (var i = 0; i < awesomeIconsArray.length; i++) {
                var ic = awesomeIconsArray[i];
                if (ctThisPanel >= maxPerPanel) {
                    // new panel
                    var child = $tc[panelnum++];
                    child.innerHTML = sCurrent + "</tr></table></center>";
                    sCurrent = "<center><table><tr>";
                    ctThisPanel = 0;
                    ctThisRow = 0;

                    // set tab title
                    $(tt[panelnum]).html("<i class=\"fa " + ic.name + "\"></i>&nbsp;");
                }

                if (ctThisRow >= maxPerRow) {
                    // new row
                    sCurrent += "</tr><tr>";
                    ctThisRow = 0;
                }

                sCurrent += "<td class=\"tdIcon\"><a href=\"#\" title=\"" + ic.name + "\"><i class=\"fa " + ic.name + "\"></i></a></td>"
                ctThisPanel++;
                ctThisRow++
            }

            if (sCurrent.length > 0) {
                $($tc[panelnum]).html((sCurrent + "</tr></table></center>"));
            }
        }

          // array containing all font awesome icons, v4.0.3.
    var awesomeIconsArray = [
                {
                    "num": "&#xf000;",
                    "name": "fa-glass"
                    },
                {
                    "num": "&#xf001;",
                    "name": "fa-music"
                    },
                {
                    "num": "&#xf002;",
                    "name": "fa-search"
                    },
                {
                    "num": "&#xf003;",
                    "name": "fa-envelope-o"
                    },
                {
                    "num": "&#xf004;",
                    "name": "fa-heart"
                    },
                {
                    "num": "&#xf005;",
                    "name": "fa-star"
                    },
                {
                    "num": "&#xf006;",
                    "name": "fa-star-o"
                    },
                {
                    "num": "&#xf007;",
                    "name": "fa-user"
                    },
                {
                    "num": "&#xf008;",
                    "name": "fa-film"
                    },
                {
                    "num": "&#xf009;",
                    "name": "fa-th-large"
                    },
                {
                    "num": "&#xf00a;",
                    "name": "fa-th"
                    },
                {
                    "num": "&#xf00b;",
                    "name": "fa-th-list"
                    },
                {
                    "num": "&#xf00c;",
                    "name": "fa-check"
                    },
                {
                    "num": "&#xf00d;",
                    "name": "fa-times"
                    },
                {
                    "num": "&#xf00e;",
                    "name": "fa-search-plus"
                    },
                {
                    "num": "&#xf010;",
                    "name": "fa-search-minus"
                    },
                {
                    "num": "&#xf011;",
                    "name": "fa-power-off"
                    },
                {
                    "num": "&#xf012;",
                    "name": "fa-signal"
                    },
                {
                    "num": "&#xf013;",
                    "name": "fa-gear"
                    },
                {
                    "num": "&#xf014;",
                    "name": "fa-trash-o"
                    },
                {
                    "num": "&#xf015;",
                    "name": "fa-home"
                    },
                {
                    "num": "&#xf016;",
                    "name": "fa-file-o"
                    },
                {
                    "num": "&#xf017;",
                    "name": "fa-clock-o"
                    },
                {
                    "num": "&#xf018;",
                    "name": "fa-road"
                    },
                {
                    "num": "&#xf019;",
                    "name": "fa-download"
                    },
                {
                    "num": "&#xf01a;",
                    "name": "fa-arrow-circle-o-down"
                    },
                {
                    "num": "&#xf01b;",
                    "name": "fa-arrow-circle-o-up"
                    },
                {
                    "num": "&#xf01c;",
                    "name": "fa-inbox"
                    },
                {
                    "num": "&#xf01d;",
                    "name": "fa-play-circle-o"
                    },
                {
                    "num": "&#xf01e;",
                    "name": "fa-rotate-right"
                    },
                {
                    "num": "&#xf021;",
                    "name": "fa-refresh"
                    },
                {
                    "num": "&#xf022;",
                    "name": "fa-list-alt"
                    },
                {
                    "num": "&#xf023;",
                    "name": "fa-lock"
                    },
                {
                    "num": "&#xf024;",
                    "name": "fa-flag"
                    },
                {
                    "num": "&#xf025;",
                    "name": "fa-headphones"
                    },
                {
                    "num": "&#xf026;",
                    "name": "fa-volume-off"
                    },
                {
                    "num": "&#xf027;",
                    "name": "fa-volume-down"
                    },
                {
                    "num": "&#xf028;",
                    "name": "fa-volume-up"
                    },
                {
                    "num": "&#xf029;",
                    "name": "fa-qrcode"
                    },
                {
                    "num": "&#xf02a;",
                    "name": "fa-barcode"
                    },
                {
                    "num": "&#xf02b;",
                    "name": "fa-tag"
                    },
                {
                    "num": "&#xf02c;",
                    "name": "fa-tags"
                    },
                {
                    "num": "&#xf02d;",
                    "name": "fa-book"
                    },
                {
                    "num": "&#xf02e;",
                    "name": "fa-bookmark"
                    },
                {
                    "num": "&#xf02f;",
                    "name": "fa-print"
                    },
                {
                    "num": "&#xf030;",
                    "name": "fa-camera"
                    },
                {
                    "num": "&#xf031;",
                    "name": "fa-font"
                    },
                {
                    "num": "&#xf032;",
                    "name": "fa-bold"
                    },
                {
                    "num": "&#xf033;",
                    "name": "fa-italic"
                    },
                {
                    "num": "&#xf034;",
                    "name": "fa-text-height"
                    },
                {
                    "num": "&#xf035;",
                    "name": "fa-text-width"
                    },
                {
                    "num": "&#xf036;",
                    "name": "fa-align-left"
                    },
                {
                    "num": "&#xf037;",
                    "name": "fa-align-center"
                    },
                {
                    "num": "&#xf038;",
                    "name": "fa-align-right"
                    },
                {
                    "num": "&#xf039;",
                    "name": "fa-align-justify"
                    },
                {
                    "num": "&#xf03a;",
                    "name": "fa-list"
                    },
                {
                    "num": "&#xf03b;",
                    "name": "fa-dedent"
                    },
                {
                    "num": "&#xf03c;",
                    "name": "fa-indent"
                    },
                {
                    "num": "&#xf03d;",
                    "name": "fa-video-camera"
                    },
                {
                    "num": "&#xf03e;",
                    "name": "fa-picture-o"
                    },
                {
                    "num": "&#xf040;",
                    "name": "fa-pencil"
                    },
                {
                    "num": "&#xf041;",
                    "name": "fa-map-marker"
                    },
                {
                    "num": "&#xf042;",
                    "name": "fa-adjust"
                    },
                {
                    "num": "&#xf043;",
                    "name": "fa-tint"
                    },
                {
                    "num": "&#xf044;",
                    "name": "fa-edit"
                    },
                {
                    "num": "&#xf045;",
                    "name": "fa-share-square-o"
                    },
                {
                    "num": "&#xf046;",
                    "name": "fa-check-square-o"
                    },
                {
                    "num": "&#xf047;",
                    "name": "fa-arrows"
                    },
                {
                    "num": "&#xf048;",
                    "name": "fa-step-backward"
                    },
                {
                    "num": "&#xf049;",
                    "name": "fa-fast-backward"
                    },
                {
                    "num": "&#xf04a;",
                    "name": "fa-backward"
                    },
                {
                    "num": "&#xf04b;",
                    "name": "fa-play"
                    },
                {
                    "num": "&#xf04c;",
                    "name": "fa-pause"
                    },
                {
                    "num": "&#xf04d;",
                    "name": "fa-stop"
                    },
                {
                    "num": "&#xf04e;",
                    "name": "fa-forward"
                    },
                {
                    "num": "&#xf050;",
                    "name": "fa-fast-forward"
                    },
                {
                    "num": "&#xf051;",
                    "name": "fa-step-forward"
                    },
                {
                    "num": "&#xf052;",
                    "name": "fa-eject"
                    },
                {
                    "num": "&#xf053;",
                    "name": "fa-chevron-left"
                    },
                {
                    "num": "&#xf054;",
                    "name": "fa-chevron-right"
                    },
                {
                    "num": "&#xf055;",
                    "name": "fa-plus-circle"
                    },
                {
                    "num": "&#xf056;",
                    "name": "fa-minus-circle"
                    },
                {
                    "num": "&#xf057;",
                    "name": "fa-times-circle"
                    },
                {
                    "num": "&#xf058;",
                    "name": "fa-check-circle"
                    },
                {
                    "num": "&#xf059;",
                    "name": "fa-question-circle"
                    },
                {
                    "num": "&#xf05a;",
                    "name": "fa-info-circle"
                    },
                {
                    "num": "&#xf05b;",
                    "name": "fa-crosshairs"
                    },
                {
                    "num": "&#xf05c;",
                    "name": "fa-times-circle-o"
                    },
                {
                    "num": "&#xf05d;",
                    "name": "fa-check-circle-o"
                    },
                {
                    "num": "&#xf05e;",
                    "name": "fa-ban"
                    },
                {
                    "num": "&#xf060;",
                    "name": "fa-arrow-left"
                    },
                {
                    "num": "&#xf061;",
                    "name": "fa-arrow-right"
                    },
                {
                    "num": "&#xf062;",
                    "name": "fa-arrow-up"
                    },
                {
                    "num": "&#xf063;",
                    "name": "fa-arrow-down"
                    },
                {
                    "num": "&#xf064;",
                    "name": "fa-mail-forward"
                    },
                {
                    "num": "&#xf065;",
                    "name": "fa-expand"
                    },
                {
                    "num": "&#xf066;",
                    "name": "fa-compress"
                    },
                {
                    "num": "&#xf067;",
                    "name": "fa-plus"
                    },
                {
                    "num": "&#xf068;",
                    "name": "fa-minus"
                    },
                {
                    "num": "&#xf069;",
                    "name": "fa-asterisk"
                    },
                {
                    "num": "&#xf06a;",
                    "name": "fa-exclamation-circle"
                    },
                {
                    "num": "&#xf06b;",
                    "name": "fa-gift"
                    },
                {
                    "num": "&#xf06c;",
                    "name": "fa-leaf"
                    },
                {
                    "num": "&#xf06d;",
                    "name": "fa-fire"
                    },
                {
                    "num": "&#xf06e;",
                    "name": "fa-eye"
                    },
                {
                    "num": "&#xf070;",
                    "name": "fa-eye-slash"
                    },
                {
                    "num": "&#xf071;",
                    "name": "fa-warning"
                    },
                {
                    "num": "&#xf072;",
                    "name": "fa-plane"
                    },
                {
                    "num": "&#xf073;",
                    "name": "fa-calendar"
                    },
                {
                    "num": "&#xf074;",
                    "name": "fa-random"
                    },
                {
                    "num": "&#xf075;",
                    "name": "fa-comment"
                    },
                {
                    "num": "&#xf076;",
                    "name": "fa-magnet"
                    },
                {
                    "num": "&#xf077;",
                    "name": "fa-chevron-up"
                    },
                {
                    "num": "&#xf078;",
                    "name": "fa-chevron-down"
                    },
                {
                    "num": "&#xf079;",
                    "name": "fa-retweet"
                    },
                {
                    "num": "&#xf07a;",
                    "name": "fa-shopping-cart"
                    },
                {
                    "num": "&#xf07b;",
                    "name": "fa-folder"
                    },
                {
                    "num": "&#xf07c;",
                    "name": "fa-folder-open"
                    },
                {
                    "num": "&#xf07d;",
                    "name": "fa-arrows-v"
                    },
                {
                    "num": "&#xf07e;",
                    "name": "fa-arrows-h"
                    },
                {
                    "num": "&#xf080;",
                    "name": "fa-bar-chart-o"
                    },
                {
                    "num": "&#xf081;",
                    "name": "fa-twitter-square"
                    },
                {
                    "num": "&#xf082;",
                    "name": "fa-facebook-square"
                    },
                {
                    "num": "&#xf083;",
                    "name": "fa-camera-retro"
                    },
                {
                    "num": "&#xf084;",
                    "name": "fa-key"
                    },
                {
                    "num": "&#xf085;",
                    "name": "fa-gears"
                    },
                {
                    "num": "&#xf086;",
                    "name": "fa-comments"
                    },
                {
                    "num": "&#xf087;",
                    "name": "fa-thumbs-o-up"
                    },
                {
                    "num": "&#xf088;",
                    "name": "fa-thumbs-o-down"
                    },
                {
                    "num": "&#xf089;",
                    "name": "fa-star-half"
                    },
                {
                    "num": "&#xf08a;",
                    "name": "fa-heart-o"
                    },
                {
                    "num": "&#xf08b;",
                    "name": "fa-sign-out"
                    },
                {
                    "num": "&#xf08c;",
                    "name": "fa-linkedin-square"
                    },
                {
                    "num": "&#xf08d;",
                    "name": "fa-thumb-tack"
                    },
                {
                    "num": "&#xf08e;",
                    "name": "fa-external-link"
                    },
                {
                    "num": "&#xf090;",
                    "name": "fa-sign-in"
                    },
                {
                    "num": "&#xf091;",
                    "name": "fa-trophy"
                    },
                {
                    "num": "&#xf092;",
                    "name": "fa-github-square"
                    },
                {
                    "num": "&#xf093;",
                    "name": "fa-upload"
                    },
                {
                    "num": "&#xf094;",
                    "name": "fa-lemon-o"
                    },
                {
                    "num": "&#xf095;",
                    "name": "fa-phone"
                    },
                {
                    "num": "&#xf096;",
                    "name": "fa-square-o"
                    },
                {
                    "num": "&#xf097;",
                    "name": "fa-bookmark-o"
                    },
                {
                    "num": "&#xf098;",
                    "name": "fa-phone-square"
                    },
                {
                    "num": "&#xf099;",
                    "name": "fa-twitter"
                    },
                {
                    "num": "&#xf09a;",
                    "name": "fa-facebook"
                    },
                {
                    "num": "&#xf09b;",
                    "name": "fa-github"
                    },
                {
                    "num": "&#xf09c;",
                    "name": "fa-unlock"
                    },
                {
                    "num": "&#xf09d;",
                    "name": "fa-credit-card"
                    },
                {
                    "num": "&#xf09e;",
                    "name": "fa-rss"
                    },
                {
                    "num": "&#xf0a0;",
                    "name": "fa-hdd-o"
                    },
                {
                    "num": "&#xf0a1;",
                    "name": "fa-bullhorn"
                    },
                {
                    "num": "&#xf0f3;",
                    "name": "fa-bell"
                    },
                {
                    "num": "&#xf0a3;",
                    "name": "fa-certificate"
                    },
                {
                    "num": "&#xf0a4;",
                    "name": "fa-hand-o-right"
                    },
                {
                    "num": "&#xf0a5;",
                    "name": "fa-hand-o-left"
                    },
                {
                    "num": "&#xf0a6;",
                    "name": "fa-hand-o-up"
                    },
                {
                    "num": "&#xf0a7;",
                    "name": "fa-hand-o-down"
                    },
                {
                    "num": "&#xf0a8;",
                    "name": "fa-arrow-circle-left"
                    },
                {
                    "num": "&#xf0a9;",
                    "name": "fa-arrow-circle-right"
                    },
                {
                    "num": "&#xf0aa;",
                    "name": "fa-arrow-circle-up"
                    },
                {
                    "num": "&#xf0ab;",
                    "name": "fa-arrow-circle-down"
                    },
                {
                    "num": "&#xf0ac;",
                    "name": "fa-globe"
                    },
                {
                    "num": "&#xf0ad;",
                    "name": "fa-wrench"
                    },
                {
                    "num": "&#xf0ae;",
                    "name": "fa-tasks"
                    },
                {
                    "num": "&#xf0b0;",
                    "name": "fa-filter"
                    },
                {
                    "num": "&#xf0b1;",
                    "name": "fa-briefcase"
                    },
                {
                    "num": "&#xf0b2;",
                    "name": "fa-arrows-alt"
                    },
                {
                    "num": "&#xf0c0;",
                    "name": "fa-group"
                    },
                {
                    "num": "&#xf0c1;",
                    "name": "fa-chain"
                    },
                {
                    "num": "&#xf0c2;",
                    "name": "fa-cloud"
                    },
                {
                    "num": "&#xf0c3;",
                    "name": "fa-flask"
                    },
                {
                    "num": "&#xf0c4;",
                    "name": "fa-cut"
                    },
                {
                    "num": "&#xf0c5;",
                    "name": "fa-copy"
                    },
                {
                    "num": "&#xf0c6;",
                    "name": "fa-paperclip"
                    },
                {
                    "num": "&#xf0c7;",
                    "name": "fa-save"
                    },
                {
                    "num": "&#xf0c8;",
                    "name": "fa-square"
                    },
                {
                    "num": "&#xf0c9;",
                    "name": "fa-bars"
                    },
                {
                    "num": "&#xf0ca;",
                    "name": "fa-list-ul"
                    },
                {
                    "num": "&#xf0cb;",
                    "name": "fa-list-ol"
                    },
                {
                    "num": "&#xf0cc;",
                    "name": "fa-strikethrough"
                    },
                {
                    "num": "&#xf0cd;",
                    "name": "fa-underline"
                    },
                {
                    "num": "&#xf0ce;",
                    "name": "fa-table"
                    },
                {
                    "num": "&#xf0d0;",
                    "name": "fa-magic"
                    },
                {
                    "num": "&#xf0d1;",
                    "name": "fa-truck"
                    },
                {
                    "num": "&#xf0d2;",
                    "name": "fa-pinterest"
                    },
                {
                    "num": "&#xf0d3;",
                    "name": "fa-pinterest-square"
                    },
                {
                    "num": "&#xf0d4;",
                    "name": "fa-google-plus-square"
                    },
                {
                    "num": "&#xf0d5;",
                    "name": "fa-google-plus"
                    },
                {
                    "num": "&#xf0d6;",
                    "name": "fa-money"
                    },
                {
                    "num": "&#xf0d7;",
                    "name": "fa-caret-down"
                    },
                {
                    "num": "&#xf0d8;",
                    "name": "fa-caret-up"
                    },
                {
                    "num": "&#xf0d9;",
                    "name": "fa-caret-left"
                    },
                {
                    "num": "&#xf0da;",
                    "name": "fa-caret-right"
                    },
                {
                    "num": "&#xf0db;",
                    "name": "fa-columns"
                    },
                {
                    "num": "&#xf0dc;",
                    "name": "fa-unsorted"
                    },
                {
                    "num": "&#xf0dd;",
                    "name": "fa-sort-down"
                    },
                {
                    "num": "&#xf0de;",
                    "name": "fa-sort-up"
                    },
                {
                    "num": "&#xf0e0;",
                    "name": "fa-envelope"
                    },
                {
                    "num": "&#xf0e1;",
                    "name": "fa-linkedin"
                    },
                {
                    "num": "&#xf0e2;",
                    "name": "fa-rotate-left"
                    },
                {
                    "num": "&#xf0e3;",
                    "name": "fa-legal"
                    },
                {
                    "num": "&#xf0e4;",
                    "name": "fa-dashboard"
                    },
                {
                    "num": "&#xf0e5;",
                    "name": "fa-comment-o"
                    },
                {
                    "num": "&#xf0e6;",
                    "name": "fa-comments-o"
                    },
                {
                    "num": "&#xf0e7;",
                    "name": "fa-flash"
                    },
                {
                    "num": "&#xf0e8;",
                    "name": "fa-sitemap"
                    },
                {
                    "num": "&#xf0e9;",
                    "name": "fa-umbrella"
                    },
                {
                    "num": "&#xf0ea;",
                    "name": "fa-paste"
                    },
                {
                    "num": "&#xf0eb;",
                    "name": "fa-lightbulb-o"
                    },
                {
                    "num": "&#xf0ec;",
                    "name": "fa-exchange"
                    },
                {
                    "num": "&#xf0ed;",
                    "name": "fa-cloud-download"
                    },
                {
                    "num": "&#xf0ee;",
                    "name": "fa-cloud-upload"
                    },
                {
                    "num": "&#xf0f0;",
                    "name": "fa-user-md"
                    },
                {
                    "num": "&#xf0f1;",
                    "name": "fa-stethoscope"
                    },
                {
                    "num": "&#xf0f2;",
                    "name": "fa-suitcase"
                    },
                {
                    "num": "&#xf0a2;",
                    "name": "fa-bell-o"
                    },
                {
                    "num": "&#xf0f4;",
                    "name": "fa-coffee"
                    },
                {
                    "num": "&#xf0f5;",
                    "name": "fa-cutlery"
                    },
                {
                    "num": "&#xf0f6;",
                    "name": "fa-file-text-o"
                    },
                {
                    "num": "&#xf0f7;",
                    "name": "fa-building-o"
                    },
                {
                    "num": "&#xf0f8;",
                    "name": "fa-hospital-o"
                    },
                {
                    "num": "&#xf0f9;",
                    "name": "fa-ambulance"
                    },
                {
                    "num": "&#xf0fa;",
                    "name": "fa-medkit"
                    },
                {
                    "num": "&#xf0fb;",
                    "name": "fa-fighter-jet"
                    },
                {
                    "num": "&#xf0fc;",
                    "name": "fa-beer"
                    },
                {
                    "num": "&#xf0fd;",
                    "name": "fa-h-square"
                    },
                {
                    "num": "&#xf0fe;",
                    "name": "fa-plus-square"
                    },
                {
                    "num": "&#xf100;",
                    "name": "fa-angle-double-left"
                    },
                {
                    "num": "&#xf101;",
                    "name": "fa-angle-double-right"
                    },
                {
                    "num": "&#xf102;",
                    "name": "fa-angle-double-up"
                    },
                {
                    "num": "&#xf103;",
                    "name": "fa-angle-double-down"
                    },
                {
                    "num": "&#xf104;",
                    "name": "fa-angle-left"
                    },
                {
                    "num": "&#xf105;",
                    "name": "fa-angle-right"
                    },
                {
                    "num": "&#xf106;",
                    "name": "fa-angle-up"
                    },
                {
                    "num": "&#xf107;",
                    "name": "fa-angle-down"
                    },
                {
                    "num": "&#xf108;",
                    "name": "fa-desktop"
                    },
                {
                    "num": "&#xf109;",
                    "name": "fa-laptop"
                    },
                {
                    "num": "&#xf10a;",
                    "name": "fa-tablet"
                    },
                {
                    "num": "&#xf10b;",
                    "name": "fa-mobile-phone"
                    },
                {
                    "num": "&#xf10c;",
                    "name": "fa-circle-o"
                    },
                {
                    "num": "&#xf10d;",
                    "name": "fa-quote-left"
                    },
                {
                    "num": "&#xf10e;",
                    "name": "fa-quote-right"
                    },
                {
                    "num": "&#xf110;",
                    "name": "fa-spinner"
                    },
                {
                    "num": "&#xf111;",
                    "name": "fa-circle"
                    },
                {
                    "num": "&#xf112;",
                    "name": "fa-mail-reply"
                    },
                {
                    "num": "&#xf113;",
                    "name": "fa-github-alt"
                    },
                {
                    "num": "&#xf114;",
                    "name": "fa-folder-o"
                    },
                {
                    "num": "&#xf115;",
                    "name": "fa-folder-open-o"
                    },
                {
                    "num": "&#xf118;",
                    "name": "fa-smile-o"
                    },
                {
                    "num": "&#xf119;",
                    "name": "fa-frown-o"
                    },
                {
                    "num": "&#xf11a;",
                    "name": "fa-meh-o"
                    },
                {
                    "num": "&#xf11b;",
                    "name": "fa-gamepad"
                    },
                {
                    "num": "&#xf11c;",
                    "name": "fa-keyboard-o"
                    },
                {
                    "num": "&#xf11d;",
                    "name": "fa-flag-o"
                    },
                {
                    "num": "&#xf11e;",
                    "name": "fa-flag-checkered"
                    },
                {
                    "num": "&#xf120;",
                    "name": "fa-terminal"
                    },
                {
                    "num": "&#xf121;",
                    "name": "fa-code"
                    },
                {
                    "num": "&#xf122;",
                    "name": "fa-reply-all"
                    },
                {
                    "num": "&#xf122;",
                    "name": "fa-mail-reply-all"
                    },
                {
                    "num": "&#xf123;",
                    "name": "fa-star-half-empty"
                    },
                {
                    "num": "&#xf124;",
                    "name": "fa-location-arrow"
                    },
                {
                    "num": "&#xf125;",
                    "name": "fa-crop"
                    },
                {
                    "num": "&#xf126;",
                    "name": "fa-code-fork"
                    },
                {
                    "num": "&#xf127;",
                    "name": "fa-unlink"
                    },
                {
                    "num": "&#xf128;",
                    "name": "fa-question"
                    },
                {
                    "num": "&#xf129;",
                    "name": "fa-info"
                    },
                {
                    "num": "&#xf12a;",
                    "name": "fa-exclamation"
                    },
                {
                    "num": "&#xf12b;",
                    "name": "fa-superscript"
                    },
                {
                    "num": "&#xf12c;",
                    "name": "fa-subscript"
                    },
                {
                    "num": "&#xf12d;",
                    "name": "fa-eraser"
                    },
                {
                    "num": "&#xf12e;",
                    "name": "fa-puzzle-piece"
                    },
                {
                    "num": "&#xf130;",
                    "name": "fa-microphone"
                    },
                {
                    "num": "&#xf131;",
                    "name": "fa-microphone-slash"
                    },
                {
                    "num": "&#xf132;",
                    "name": "fa-shield"
                    },
                {
                    "num": "&#xf133;",
                    "name": "fa-calendar-o"
                    },
                {
                    "num": "&#xf134;",
                    "name": "fa-fire-extinguisher"
                    },
                {
                    "num": "&#xf135;",
                    "name": "fa-rocket"
                    },
                {
                    "num": "&#xf136;",
                    "name": "fa-maxcdn"
                    },
                {
                    "num": "&#xf137;",
                    "name": "fa-chevron-circle-left"
                    },
                {
                    "num": "&#xf138;",
                    "name": "fa-chevron-circle-right"
                    },
                {
                    "num": "&#xf139;",
                    "name": "fa-chevron-circle-up"
                    },
                {
                    "num": "&#xf13a;",
                    "name": "fa-chevron-circle-down"
                    },
                {
                    "num": "&#xf13b;",
                    "name": "fa-html5"
                    },
                {
                    "num": "&#xf13c;",
                    "name": "fa-css3"
                    },
                {
                    "num": "&#xf13d;",
                    "name": "fa-anchor"
                    },
                {
                    "num": "&#xf13e;",
                    "name": "fa-unlock-alt"
                    },
                {
                    "num": "&#xf140;",
                    "name": "fa-bullseye"
                    },
                {
                    "num": "&#xf141;",
                    "name": "fa-ellipsis-h"
                    },
                {
                    "num": "&#xf142;",
                    "name": "fa-ellipsis-v"
                    },
                {
                    "num": "&#xf143;",
                    "name": "fa-rss-square"
                    },
                {
                    "num": "&#xf144;",
                    "name": "fa-play-circle"
                    },
                {
                    "num": "&#xf145;",
                    "name": "fa-ticket"
                    },
                {
                    "num": "&#xf146;",
                    "name": "fa-minus-square"
                    },
                {
                    "num": "&#xf147;",
                    "name": "fa-minus-square-o"
                    },
                {
                    "num": "&#xf148;",
                    "name": "fa-level-up"
                    },
                {
                    "num": "&#xf149;",
                    "name": "fa-level-down"
                    },
                {
                    "num": "&#xf14a;",
                    "name": "fa-check-square"
                    },
                {
                    "num": "&#xf14b;",
                    "name": "fa-pencil-square"
                    },
                {
                    "num": "&#xf14c;",
                    "name": "fa-external-link-square"
                    },
                {
                    "num": "&#xf14d;",
                    "name": "fa-share-square"
                    },
                {
                    "num": "&#xf14e;",
                    "name": "fa-compass"
                    },
                {
                    "num": "&#xf150;",
                    "name": "fa-toggle-down"
                    },
                {
                    "num": "&#xf151;",
                    "name": "fa-toggle-up"
                    },
                {
                    "num": "&#xf152;",
                    "name": "fa-toggle-right"
                    },
                {
                    "num": "&#xf153;",
                    "name": "fa-euro"
                    },
                {
                    "num": "&#xf154;",
                    "name": "fa-gbp"
                    },
                {
                    "num": "&#xf155;",
                    "name": "fa-dollar"
                    },
                {
                    "num": "&#xf156;",
                    "name": "fa-rupee"
                    },
                {
                    "num": "&#xf157;",
                    "name": "fa-cny"
                    },
                {
                    "num": "&#xf158;",
                    "name": "fa-ruble"
                    },
                {
                    "num": "&#xf159;",
                    "name": "fa-won"
                    },
                {
                    "num": "&#xf15a;",
                    "name": "fa-bitcoin"
                    },
                {
                    "num": "&#xf15b;",
                    "name": "fa-file"
                    },
                {
                    "num": "&#xf15c;",
                    "name": "fa-file-text"
                    },
                {
                    "num": "&#xf15d;",
                    "name": "fa-sort-alpha-asc"
                    },
                {
                    "num": "&#xf15e;",
                    "name": "fa-sort-alpha-desc"
                    },
                {
                    "num": "&#xf160;",
                    "name": "fa-sort-amount-asc"
                    },
                {
                    "num": "&#xf161;",
                    "name": "fa-sort-amount-desc"
                    },
                {
                    "num": "&#xf162;",
                    "name": "fa-sort-numeric-asc"
                    },
                {
                    "num": "&#xf163;",
                    "name": "fa-sort-numeric-desc"
                    },
                {
                    "num": "&#xf164;",
                    "name": "fa-thumbs-up"
                    },
                {
                    "num": "&#xf165;",
                    "name": "fa-thumbs-down"
                    },
                {
                    "num": "&#xf166;",
                    "name": "fa-youtube-square"
                    },
                {
                    "num": "&#xf167;",
                    "name": "fa-youtube"
                    },
                {
                    "num": "&#xf168;",
                    "name": "fa-xing"
                    },
                {
                    "num": "&#xf169;",
                    "name": "fa-xing-square"
                    },
                {
                    "num": "&#xf16a;",
                    "name": "fa-youtube-play"
                    },
                {
                    "num": "&#xf16b;",
                    "name": "fa-dropbox"
                    },
                {
                    "num": "&#xf16c;",
                    "name": "fa-stack-overflow"
                    },
                {
                    "num": "&#xf16d;",
                    "name": "fa-instagram"
                    },
                {
                    "num": "&#xf16e;",
                    "name": "fa-flickr"
                    },
                {
                    "num": "&#xf170;",
                    "name": "fa-adn"
                    },
                {
                    "num": "&#xf171;",
                    "name": "fa-bitbucket"
                    },
                {
                    "num": "&#xf172;",
                    "name": "fa-bitbucket-square"
                    },
                {
                    "num": "&#xf173;",
                    "name": "fa-tumblr"
                    },
                {
                    "num": "&#xf174;",
                    "name": "fa-tumblr-square"
                    },
                {
                    "num": "&#xf175;",
                    "name": "fa-long-arrow-down"
                    },
                {
                    "num": "&#xf176;",
                    "name": "fa-long-arrow-up"
                    },
                {
                    "num": "&#xf177;",
                    "name": "fa-long-arrow-left"
                    },
                {
                    "num": "&#xf178;",
                    "name": "fa-long-arrow-right"
                    },
                {
                    "num": "&#xf179;",
                    "name": "fa-apple"
                    },
                {
                    "num": "&#xf17a;",
                    "name": "fa-windows"
                    },
                {
                    "num": "&#xf17b;",
                    "name": "fa-android"
                    },
                {
                    "num": "&#xf17c;",
                    "name": "fa-linux"
                    },
                {
                    "num": "&#xf17d;",
                    "name": "fa-dribbble"
                    },
                {
                    "num": "&#xf17e;",
                    "name": "fa-skype"
                    },
                {
                    "num": "&#xf180;",
                    "name": "fa-foursquare"
                    },
                {
                    "num": "&#xf181;",
                    "name": "fa-trello"
                    },
                {
                    "num": "&#xf182;",
                    "name": "fa-female"
                    },
                {
                    "num": "&#xf183;",
                    "name": "fa-male"
                    },
                {
                    "num": "&#xf184;",
                    "name": "fa-gittip"
                    },
                {
                    "num": "&#xf185;",
                    "name": "fa-sun-o"
                    },
                {
                    "num": "&#xf186;",
                    "name": "fa-moon-o"
                    },
                {
                    "num": "&#xf187;",
                    "name": "fa-archive"
                    },
                {
                    "num": "&#xf188;",
                    "name": "fa-bug"
                    },
                {
                    "num": "&#xf189;",
                    "name": "fa-vk"
                    },
                {
                    "num": "&#xf18a;",
                    "name": "fa-weibo"
                    },
                {
                    "num": "&#xf18b;",
                    "name": "fa-renren"
                    },
                {
                    "num": "&#xf18c;",
                    "name": "fa-pagelines"
                    },
                {
                    "num": "&#xf18d;",
                    "name": "fa-stack-exchange"
                    },
                {
                    "num": "&#xf18e;",
                    "name": "fa-arrow-circle-o-right"
                    },
                {
                    "num": "&#xf190;",
                    "name": "fa-arrow-circle-o-left"
                    },
                {
                    "num": "&#xf191;",
                    "name": "fa-toggle-left"
                    },
                {
                    "num": "&#xf192;",
                    "name": "fa-dot-circle-o"
                    },
                {
                    "num": "&#xf193;",
                    "name": "fa-wheelchair"
                    },
                {
                    "num": "&#xf194;",
                    "name": "fa-vimeo-square"
                    },
                {
                    "num": "&#xf195;",
                    "name": "fa-turkish-lira"
                    },
                {
                    "num": "&#xf196;",
                    "name": "fa-plus-square-o"
                    }
                ]


});
