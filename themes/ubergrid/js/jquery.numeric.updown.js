"use strict";
(function($){

    var bigNum = 10;
    var smallNum = 1;
    
    $.fn.numericUpDown = function(){
        this.each(function(index){
            $(this).attr('readonly', 'readonly');
            var val = $(this).val();
            val = parseInt(val);
            if(isNaN(val)){
                val = 0;
            }
            $(this).val(val);
            $(this).css('width', '80px');
            var div = document.createElement('div');
            div.className = 'num-up-down';
            $(this).before(div);
            $(div).append($(this));
            
            var bigPlus = document.createElement('div');
            bigPlus.className = 'num-up-down-bp';
            bigPlus.innerHTML = '+';
            $(div).append(bigPlus);
            
            var smallPlus = document.createElement('div');
            smallPlus.className = 'num-up-down-sp';
            smallPlus.innerHTML = '+';
            $(div).append(smallPlus);
            
            var bigMinus = document.createElement('div');
            bigMinus.className = 'num-up-down-bm';
            bigMinus.innerHTML = '-';
            $(div).append(bigMinus);
                                    
            var smallMinus = document.createElement('div');
            smallMinus.className = 'num-up-down-sm';
            smallMinus.innerHTML = '-';
            $(div).append(smallMinus);            
        });        
    }
    
    $(document).on('click', '.num-up-down-bp', function(e){
        addToInput($(this).parent(), bigNum); 
    });
    
    $(document).on('click', '.num-up-down-bm', function(e){
        addToInput($(this).parent(), 0-bigNum); 
    });
    
    $(document).on('click', '.num-up-down-sp', function(e){
        addToInput($(this).parent(), smallNum); 
    });
    
    $(document).on('click', '.num-up-down-sm', function(e){
        addToInput($(this).parent(), 0-smallNum); 
    });
    
    function addToInput(parent, num){
        var input = $(parent).children('input').get();
        input = input[0];
        var val = $(input).val();
        val = parseInt(val);
        if(isNaN(val)){
            val = 0;
        }
        val = val + num;
        $(input).val(val).change();
    }
}(jQuery));
