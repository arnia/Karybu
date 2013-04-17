function getDashboardContentHeight(){
    return Math.floor(($(window).height() - 80))
}

function dynamicSize() {

    $('#dashboard_content').css({
        'height': getDashboardContentHeight()
    });
}

jQuery(document).ready( function() {
    $ = jQuery;
    dynamicSize();
})

jQuery(window).bind('resize', function(){
    dynamicSize();
});