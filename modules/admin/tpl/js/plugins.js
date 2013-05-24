(function($){

    $.fn.scrollableTable = function() {
        return this.each(function() {
            $(this).wrap("<div class='horizontal-scroll'></div>");
            $(this).parent().mCustomScrollbar({
                horizontalScroll:true,
                mouseWheel:false
            });
        });
    }

}(jQuery));