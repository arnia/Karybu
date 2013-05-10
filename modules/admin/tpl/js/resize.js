(function($){

    /**
     * Karybu admin has three areas:
     *   - sitemap navigation - #kSidebar
     *   - main content area - #dashboard_content
     *   - actions navigation - #kNav (members list, layout info etc)
     *
     */

    /**
     * Sitemap navigation (left sidebar)
     */
    function kSitemapNav() {}
    kSitemapNav.initializeScrollbar = function() {
        $("#kSidebar").mCustomScrollbar({
            scrollButtons:{
                enable:true
            },
            advanced:{
                updateOnContentResize: true
            }
        });
    }

    /**
     * Actions navigation (right sidebar)
     */
    function kActionsNav() {}
    kActionsNav.initializeScrollbar = function() {
        if(isSmallScreen()) {
            kActionsNav.initializeHorizontalScrollbar();
        } else {
            kActionsNav.initializeVerticalScrollbar();
        }
    }
    kActionsNav.initializeVerticalScrollbar = function() {
        $("#kNav").mCustomScrollbar({
            scrollButtons:{
                enable:true
            },
            advanced:{
                updateOnContentResize: true
            }
        });
    }
    kActionsNav.initializeHorizontalScrollbar = function() {
        $("#kNav").mCustomScrollbar({
            horizontalScroll:true,
            scrollButtons: {
                enable: true
            },
            advanced:{
                updateOnContentResize: true
            }
        });
    }
    kActionsNav.makeHorizontal = function() {
        $("#kNav").mCustomScrollbar("destroy");
        kActionsNav.initializeHorizontalScrollbar();
    }
    kActionsNav.makeVertical = function() {
        $("#kNav").mCustomScrollbar("destroy");
        kActionsNav.initializeVerticalScrollbar();
    }

    /**
     * Admin content area
     */
    function kAdminContentArea() {}
    kAdminContentArea.initializeScrollbar = function() {
        $("#dashboard_content").mCustomScrollbar({
            scrollButtons:{
                enable:true
            },
            scrollInertia:200,
            advanced:{
                updateOnContentResize: true,
                normalizeMouseWheelDelta: true
            }
        });
    }
    kAdminContentArea.updateHeight = function() {
        $('#dashboard_content').css({
            'height': getDashboardContentHeight()
        });
    }

    /**
     * Dashboard visitors graph
     */
    function kDashboardGraph() {}
    kDashboardGraph.initialize = function(){
        $("#chart").attr('width',$("#chart-container").width());
        RGraph.Redraw();
    };
    kDashboardGraph.redraw = function() {
        kDashboardGraph.initialize();
    }

    /**
     * Generic functions
     */
    function getDashboardContentHeight() {
        if ($('.main-nav').length > 0) {
            if (parseInt($(window).width()) <= 767) {
                return Math.floor(($(window).height() - 200))
            }
            else{
                return Math.floor(($(window).height() - 120))
            }
        }
        else {
            if (parseInt($(window).width()) <= 767) {
                return Math.floor(($(window).height() - 160))
            }
            else{
                    return Math.floor(($(window).height() - 80))
                }
        }
    }

    function hasNav()
    {
        return ($('.main-nav').length > 0);
    }


    function isSmallScreen()
    {
        return parseInt($(window).width()) <= 767;
    }

    function initializeScreenResolution()
    {
        var browserWidth = $(window).width();
        var browserHeight = $(window).height();

        $("body").removeClass("phone-screen tablet-screen desktop-screen large-desktop-screen");

        if(browserWidth > 1200) {
            $("body").addClass("large-desktop-screen");
        } else if(768 <= browserWidth && browserWidth <= 1200) {
            $("body").addClass("desktop-screen");
        } else if(480 <= browserWidth && browserWidth <= 767) {
            $("body").addClass("tablet-screen");
        } else {
            $("body").addClass("phone-screen");
        }
    }

    /**
     * Event handlers
     */
    $(window).load(function(){
        initializeScreenResolution();

        kAdminContentArea.updateHeight();
        kAdminContentArea.initializeScrollbar();

        kSitemapNav.initializeScrollbar();
        kActionsNav.initializeScrollbar();

        kDashboardGraph.initialize();

        if($(".main-nav ul.nav").height() > 50) {
            $(".main-nav").addClass("main-nav-stacked");
        } else {
            $(".main-nav").removeClass("main-nav-stacked");
        }

        if (hasNav()){
            $('body').addClass('kBigHeader')
        }

        $(window).resize(function(){
            initializeScreenResolution();

            kAdminContentArea.updateHeight();
            kDashboardGraph.redraw();

            if (isSmallScreen()) {
                kActionsNav.makeHorizontal();
            }
            else{
                kActionsNav.makeVertical();
            }

            if($(".main-nav ul.nav").height() > 50) {
                $(".main-nav").addClass("main-nav-stacked");
            } else {
                $(".main-nav").removeClass("main-nav-stacked");
            }
        });
    });

}(jQuery));