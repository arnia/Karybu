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
        var heightOfPageTitle = 60;
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

        $('#dashboard_content').verticalScrollbar({ updateHeight: getDashboardContentHeight});
        $('#kSidebar').verticalScrollbar();
        $('#kNav').smartScrollbar();
        $('#kMainNav').horizontalScrollbar({ autoHideScrollbar: true });

        if (hasNav()){
            $('body').addClass('kBigHeader')
        }

        $(window).resize(function(){
            initializeScreenResolution();
        });
    });

}(jQuery));