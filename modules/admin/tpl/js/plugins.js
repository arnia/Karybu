(function($){

    $.fn.scrollableTable = function() {
        return this.each(function() {
            $(this).wrap("<div class='horizontal-scroll'></div>");
            $(this).css('width', $(this).width());
            $(this).parent().mCustomScrollbar({
                horizontalScroll:true,
                mouseWheel:false
            });
        });
    }

}(jQuery));