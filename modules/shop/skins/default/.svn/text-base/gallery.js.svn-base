/**
 * Manages the gallery functionality for product images
 * @author Daniel Ionescu
 * @requires jQuery library
 */

jQuery(document).ready( function() {

    $ = jQuery;

    Gallery = function() {
        this.init();
    }

    $.extend( Gallery.prototype, {

        init: function() {
            currentView = 1;
            viewable = 4;
            obj = $('#product-images');
            main = obj.find('#product-image');
            thumbs = obj.find('#thumbnails');
            prev = obj.find('.prev');
            next = obj.find('.next');
            thumbNr = thumbs.children('img').size();
            mainImages = new Array();
            <!--@foreach($product->images as $image)-->
            mainImages.push('<img src="{$image->getThumbnailPath(340, 240)}" />');
            <!--@end-->

            prev.click($.proxy(this.goToPrev, this));
            next.click($.proxy(this.goToNext, this));

            this.hideAllImages();
            main.children(':first').show();
        },

        goToPrev: function(e) {
            if (e) e.preventDefault();
            if (currentView > 1) {
                thumbs.animate({
                     left: '+=92'
                    },
                    500,
                    'linear',
                    this.decreaseViewCounter
                );
            }
        },

        goToNext: function(e) {
            if (e) e.preventDefault();
            if (currentView < thumbNr - viewable) {
                thumbs.animate({
                        left: '-=92'
                    },
                    500,
                    'linear',
                    this.increaseViewCounter
                );
            }
        },

        increaseViewCounter: function() {
            currentView++;
        },

        decreaseViewCounter: function() {
            currentView--;
        },

        hideAllImages: function() {
            main.children().hide();
        },

        activateThumb: function() {

        }

    });

    gallery = new Gallery();

});
