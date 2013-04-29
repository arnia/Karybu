function getDashboardContentHeight(){
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


function dynamicSize() {

    $('#dashboard_content').css({
        'height': getDashboardContentHeight()
    });
}

function addStackedToNav() {
    $('.nav.nav-tabs').each( function(){
        $(this).addClass('nav-stacked');
    });
}

function removeStackedFromNav() {
    $('.nav.nav-tabs.nav-stacked').each( function(){
        $(this).removeClass('nav-stacked');
    });
}

function destroyScrollbar() {
    $("#kNav").mCustomScrollbar("destroy");
}

function rebuildHorizontalScrollbar() {
    destroyScrollbar();
    $("#kNav").mCustomScrollbar({
        horizontalScroll:true,
        scrollButtons: {
            enable: true
        }
        });
}

function rebuildVerticalScrollbar() {
    destroyScrollbar();
    $("#kNav").mCustomScrollbar({
        scrollButtons: {
            enable: true
        }
    });
}


function hasNav()
{
    return ($('.main-nav').length > 0);
}


function isSmallScreen()
{
    return parseInt($(window).width()) <= 767;
}

jQuery(document).ready( function() {
    $ = jQuery;
    dynamicSize();



    //trigger special events for phone view
    $(window).load(function(){
        $("#chart").attr('width',$("#chart-container").width());
        RGraph.Redraw();
        if (isSmallScreen()) {
            addStackedToNav();
            rebuildHorizontalScrollbar();
        };
        if (hasNav()){
            $('body').addClass('kBigHeader')
        };

    });



    $(window).resize(function(){
        $("#chart").attr('width',$("#chart-container").width());
        RGraph.Redraw();
        if (isSmallScreen()) {
            addStackedToNav();
            rebuildHorizontalScrollbar();
        }
        else{
            removeStackedFromNav();
            rebuildVerticalScrollbar();
        }

    });
});


jQuery(window).bind('resize', function () {
    dynamicSize();
});

