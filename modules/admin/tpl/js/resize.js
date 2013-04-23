function getDashboardContentHeight(){
    if (parseInt($(window).width()) <= 767) {
        return Math.floor(($(window).height() - 160))
    }
    else{
        return Math.floor(($(window).height() - 80))
        }
}


function dynamicSize() {

    $('#dashboard_content').css({
        'height': getDashboardContentHeight()
    });
}

function addStackedToNav() {
    $('.content .nav.nav-tabs').each( function(){
        $(this).addClass('nav-stacked');
    });
}

function removeStackedFromNav() {
    $('.content .nav.nav-tabs.nav-stacked').each( function(){
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
        }
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
