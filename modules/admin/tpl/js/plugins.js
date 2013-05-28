(function($){

    /**
     * Make tables horizontally scrollable if they don't fit
     */
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

    /**
     * Scrollbars
     */
    var VERTICAL_SCROLLBAR_DEFAULTS = {
        scrollInertia:300,
        scrollButtons:{
            enable:true
        },
        advanced:{
            updateOnContentResize: true
        }
    };

    var HORIZONTAL_SCROLLBAR_DEFAULTS = $.extend({}, VERTICAL_SCROLLBAR_DEFAULTS,
        {
        horizontalScroll:true
        });

    /**
     * Vertical scrollbar
     */
    $.fn.verticalScrollbar = function(scrollbar_config) {
        scrollbar_config = $.extend(scrollbar_config, VERTICAL_SCROLLBAR_DEFAULTS);

        var update_height_function = scrollbar_config.updateHeight;

        return this.each(function() {
            var scrollbar = this;

            this.initializeScrollbar = function() {
                if(!$(scrollbar).length) return;

                if(update_height_function) {
                    scrollbar.updateHeight();

                    $(window).resize(function(){
                        scrollbar.updateHeight();
                    });
                }

                $(scrollbar).mCustomScrollbar(scrollbar_config);
                $(scrollbar).mCustomScrollbar("scrollTo", ".active");
            }

            this.updateHeight = function() {
                $(scrollbar).css({
                    'height': update_height_function()
                });
            }

            // Constructor
            scrollbar.initializeScrollbar();
        });
    }

    /**
     * Horizontal scrollbar
     */
    $.fn.horizontalScrollbar = function(scrollbar_config) {
        scrollbar_config = $.extend(scrollbar_config, HORIZONTAL_SCROLLBAR_DEFAULTS);

        return this.each(function() {
            var scrollbar = this;

            this.initializeScrollbar = function() {
                if(!$(scrollbar).length) return;

                $(scrollbar).mCustomScrollbar(scrollbar_config);
                $(scrollbar).mCustomScrollbar("scrollTo", ".active");
            }

            // Constructor
            scrollbar.initializeScrollbar();
        });
    }


    $.isSmallScreen = function()
    {
        return parseInt($(window).width()) <= 767;
    }

    $.isPortrait = function()
    {
        return $("body").hasClass("portrait");
    }

    /**
     * Vertical when desktop or landscape, horizontal otherwise
     */
    $.fn.smartScrollbar = function(scrollbar_config) {

        return this.each(function() {

            this.initializeScrollbar = function() {
                var self = this;

                if($.isSmallScreen() && $.isPortrait()) {
                    $(this).horizontalScrollbar(scrollbar_config);
                } else {
                    $(this).verticalScrollbar(scrollbar_config);
                }

                $(window).resize(function(){
                    if($.isSmallScreen() && $.isPortrait()) {
                        self.makeHorizontal();
                    }
                    else {
                        self.makeVertical();
                    }
                });
            }

            this.makeHorizontal = function() {
                $(this).mCustomScrollbar("destroy");
                $(this).horizontalScrollbar(scrollbar_config);
            }

            this.makeVertical = function() {
                $(this).mCustomScrollbar("destroy");
                $(this).verticalScrollbar(scrollbar_config);
            }


            // Constructor
            this.initializeScrollbar();
        });
    }


}(jQuery));