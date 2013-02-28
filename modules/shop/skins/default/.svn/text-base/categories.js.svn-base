/**
 * Toggles categories in category menu
 * @author: Daniel Ionescu
 */
jQuery(document).ready(function($) {

    // Display selected category parent list
    function showPath(item) {

        if (item) {

            // Cycle through all parents and display all lists
            if (!item.hasClass('body-left-panel')) {

                // fixes a Firefox bug
                if (item.get(0) != undefined) {

                    if (item.get(0).tagName.toLowerCase()=='ul') {

                        item.show();
                        item.prev('span').children('span.open-sign').hide();
                        item.prev('span').children('span.close-sign').show();

                    }

                    if (item.parent().html() != null) {
                        showPath(item.parent());
                    }

                }

            }

            // If item has children also display those
            item.children('ul').show();

            return false;

        }

        return false;

    }
    showPath($('.body-left-panel li.active'));

    // Opens a sublist
    $('span.open-sign').click(function() {

        $(this).parent().next('ul').show(400);
        $(this).next().show();
        $(this).hide();

    });

    // Closes a sublist
    $('span.close-sign').click(function() {

        $(this).parent().next('ul').hide(400);
        $(this).prev().show();
        $(this).hide();

    });

});