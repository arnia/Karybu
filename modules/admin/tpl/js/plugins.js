(function($){

    $.fn.stackable = function() {
        return this.each(function() {
            var stack = function(nav_container) {
                var navbar = nav_container.find("ul.nav");

                // console.log(nav_container.width() + ' - ' + navbar.width() + ' = ' + (nav_container.width() - navbar.width()));

                if(nav_container.width() - navbar.width() <= 52) {
                    nav_container.addClass("main-nav-stacked");
                } else {
                    nav_container.removeClass("main-nav-stacked");
                }
            }

            var nav_container = $(this);
            stack(nav_container);

            $(window).on('resize', function() {
                stack(nav_container);
            });

        });
    }

}(jQuery));