/**
 * Description
 */

/*global Modernizr: true*/
/*global HametuhaGenreStatic: true*/

(function ($) {
    'use strict';


    $(document).ready(function(){

        // masonry
        var $container = $('.series__list');
        $container.imagesLoaded( function() {
            $container.masonry({
                itemSelector: '.series__item'
            });
        });

        // more
        $('a[href="#series-testimonials-list"]').click(function(e){
            e.preventDefault();
            $($(this).attr('href')).find('.hidden').removeClass('hidden');
        });

    });

})(jQuery);
