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
    $("#kNav").mCustomScrollbar({
        horizontalScroll:true,
        scrollButtons: {
            enable: true
        }
        });
}

function rebuildVerticalScrollbar() {
    $("#kNav").mCustomScrollbar({
        scrollButtons: {
            enable: true
        }
    });
}

jQuery(document).ready( function() {
    $ = jQuery;
    dynamicSize();

    //trigger special events for phone view
    $(window).load(function(){
        $("#chart").attr('width',$("#chart-container").width());
        RGraph.Redraw();
        if (parseInt($(window).width()) <= 480) {
            addStackedToNav()
        }
    });

    $(window).resize(function(){
        $("#chart").attr('width',$("#chart-container").width());
        RGraph.Redraw();
        if (parseInt($(window).width()) <= 480) {
            addStackedToNav();
        }
        else{
            removeStackedFromNav()
        }

    });




});


jQuery(window).bind('resize', function () {
    dynamicSize();
});

