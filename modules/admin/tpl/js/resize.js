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
            scrollInertia:300,
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
        if(isSmallScreen() && isPortrait()) {
            kActionsNav.initializeHorizontalScrollbar();
        } else {
            kActionsNav.initializeVerticalScrollbar();
        }
    }
    kActionsNav.initializeVerticalScrollbar = function() {
        $("#kNav").mCustomScrollbar({
            scrollInertia:300,
            scrollButtons:{
                enable:true
            },
            advanced:{
                updateOnContentResize: true
            }
        });

        $("#kNav").mCustomScrollbar("scrollTo", ".active");
    }
    kActionsNav.initializeHorizontalScrollbar = function() {
        $("#kNav").mCustomScrollbar({
            scrollInertia:300,
            horizontalScroll:true,
            scrollButtons: {
                enable: true
            },
            advanced:{
                updateOnContentResize: true
            }
        });

        $("#kNav").mCustomScrollbar("scrollTo", ".active");
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
            scrollInertia:300,
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
     * Main navigation scrollbars
     */
    function kMainNav() {}
    kMainNav.initializeScrollbar = function() {
        if(!$("#kMainNav").length) return;

        $("#kMainNav").mCustomScrollbar({
            scrollInertia:300,
            horizontalScroll:true,
            autoHideScrollbar: true,
            scrollButtons: {
                enable: false
            },
            advanced:{
                autoScrollOnFocus: true
            }
        });
        $("#kMainNav").mCustomScrollbar("scrollTo", ".active");
    }

    /**
     * Vertical scrollbars
     */

    function kVScroll() {}
    kVScroll.initializeScrollbar = function() {
        if(!$(".kVScroll").length) return;

        $(".kVScroll").mCustomScrollbar({
            scrollInertia:300,
            horizontalScroll:true,
            autoHideScrollbar: true,
            scrollButtons: {
                enable: false
            },
            advanced:{
                autoScrollOnFocus: true
            }
        });
        $(".kVScroll").mCustomScrollbar("scrollTo", ".active");
    }



    /**
     * Generic functions
     */
    function getDashboardContentHeight() {
        var windowHeight = $(window).height();
        var heightOfPageTitle = 60;
        var heightOfAdminMainNav = 40;

        var heightOfSidebarNavigationWhenMobile = 60;
        var heightOfActionsNavigationWhenMobile = 60;

        var occupiedHeight = 0;

        if(hasNav()) {
            occupiedHeight += heightOfAdminMainNav;
        }

        if(isSmallScreen() && isPortrait()) {
            occupiedHeight += heightOfSidebarNavigationWhenMobile + heightOfActionsNavigationWhenMobile;
        } else if(isSmallScreen() && !isPortrait()) {
            occupiedHeight += 0;
        } else {
            occupiedHeight += heightOfPageTitle;
        }

        return Math.floor(windowHeight - occupiedHeight);
    }

    function hasNav()
    {
        return ($('.main-nav').length > 0);
    }


    function isSmallScreen()
    {
        return parseInt($(window).width()) <= 767;
    }

    function isPortrait()
    {
        return $("body").hasClass("portrait");
    }


    function initializeScreenResolution()
    {
        var browserWidth = $(window).width();
        var browserHeight = $(window).height();

        $("body").removeClass("phone-screen tablet-screen desktop-screen large-desktop-screen portrait landscape");

        if(browserWidth > 1200) {
            $("body").addClass("large-desktop-screen");
        } else if(768 <= browserWidth && browserWidth <= 1200) {
            $("body").addClass("desktop-screen");
        } else if(480 <= browserWidth && browserWidth <= 767) {
            $("body").addClass("tablet-screen");
        } else {
            $("body").addClass("phone-screen");
        }

        if(browserWidth < browserHeight) {
            $("body").addClass("portrait");
        }
        if(browserWidth >= browserHeight) {
            $("body").addClass("landscape");
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

        if (hasNav()){
            $('body').addClass('kBigHeader')
        }

        kMainNav.initializeScrollbar();
        kVScroll.initializeScrollbar();

        $(window).resize(function(){
            initializeScreenResolution();

            kAdminContentArea.updateHeight();

            if (isSmallScreen() && isPortrait()) {
                kActionsNav.makeHorizontal();
            }
            else{
                kActionsNav.makeVertical();
            }
        });
    });

}(jQuery));