jQuery(function($){
    "use strict";

    $("#pukka-wrap").on({
        click : function(e){
            e.preventDefault();
            if( $("#"+ $(this).data("field_id") + "-input").val() == "" ){
                // title missing
                return;
            }
            //console.log("add: " + $(this).data("field_id"));
            add_input_value($(this).data("field_id"));
        }
    }, ".input-add");

    /*
    // create new widger area on 'enter'
    $("#pukka-wrap").on({
       keyup : function(e){
            var code = e.which; // recommended to use e.which, it's normalized across browsers
            
            console.log('keyup: ' + code);

            if( code==13 ){
                e.preventDefault();
            }
            
        }
    }, ".input-field-add");
    */


    $("#pukka-wrap").on({
        click : function(e){
            e.preventDefault();
            //console.log("remove: " + $(this).data("field_id"));

            $("#"+ $(this).data("field_id") + "-wrap").remove();
            
        }

    }, ".input-remove");


    function add_input_value(field_id){

        if( field_id == "" ){
            return;
        }

        var cloneIndex = $("."+ field_id ).length;
        cloneIndex++;

        var new_el_id = field_id + "-" + cloneIndex;

        var html = "<input type='text' name='pukka[" + field_id + "][]' class='" + field_id +"' " +
                   "id='" + new_el_id +"' value='" + $("#" + field_id +"-input").val() + "' readonly='readonly'>";

        // remove button
        html += "<a href='#' data-field_id='" + new_el_id + "' class='input-remove'> <i class='fa fa-times'></i></a>";

        // add elements
        $("#" + field_id + "-input").closest(".pukka-input").append("<p id='" + new_el_id + "-wrap'>" + html + "</p>");

        // clear input value
        $("#"+ field_id + "-input").val("");
    }

});