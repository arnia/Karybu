(function($){

    /**
     * Karybu admin has three areas:
     *   - sitemap navigation - #kSidebar
     *   - main content area - #dashboard_content
     *   - actions navigation - #kNav (members list, layout info etc)
     *
     */

    /**
     * Generic functions
     */
    function getDashboardContentHeight() {
        var windowHeight = $(window).height();
        var heightOfPageTitle = $('.pagetitle-fixed').height();
        var heightOfAdminMainNav = 40;

        var heightOfSidebarNavigationWhenMobile = 60;
        var heightOfActionsNavigationWhenMobile = 60;

        var occupiedHeight = 0;

        if(hasNav()) {
            occupiedHeight += heightOfAdminMainNav;
        }

        if($.isSmallScreen() && $.isPortrait()) {
            occupiedHeight += heightOfSidebarNavigationWhenMobile + heightOfActionsNavigationWhenMobile;
        } else if($.isSmallScreen() && !$.isPortrait()) {
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

    function initializeScreenResolution()
    {
        var browserWidth = $(window).width();
        var browserHeight = $(window).height();

        var body = $("body");

        body.removeClass("phone-screen tablet-screen desktop-screen large-desktop-screen portrait landscape");

        if(browserWidth < browserHeight) {
            body.addClass("portrait");
            $(".kWrapper").width("100%");
        }
        if(browserWidth >= browserHeight) {
            body.addClass("landscape");
            // Simulate the CSS calc property, which is not supported by Opera and Safari
            $(".kWrapper").width("100%");
            $(".kWrapper").width($(".kWrapper").width() - 80);
        }

        if(browserWidth > 1200) {
            body.addClass("large-desktop-screen");
            body.trigger('admin.large-desktop-screen');
        } else if(768 <= browserWidth && browserWidth <= 1200) {
            body.addClass("desktop-screen");
            body.trigger('admin.desktop-screen');
        } else if(480 <= browserWidth && browserWidth <= 767) {
            body.addClass("tablet-screen");
            body.trigger('admin.tablet-screen');
        } else {
            body.addClass("phone-screen");
            body.trigger('admin.phone-screen');
        }
    }

    /**
     * Event handlers
     */
    $(window).load(function(){

        $("body").bind('admin.large-desktop-screen admin.desktop-screen', function() {
            $("#kMobileMenu").show();

            $(".kWrapper").width("");
        });

        $("body").bind('admin.tablet-screen admin.phone-screen', function() {
            $("#kMobileMenu").hide();


        });

        initializeScreenResolution();

        $('#dashboard_content').verticalScrollbar({ updateHeight: getDashboardContentHeight});
        $('#kSidebar').verticalScrollbar();
        $('#kNav').smartScrollbar();
        $('#kMainNav').horizontalScrollbar({ autoHideScrollbar: true });

        if (hasNav()){
            $('body').addClass('kBigHeader')
        }

        $(window).on('debouncedresize', function() {
            initializeScreenResolution();
        });

    });

}(jQuery));