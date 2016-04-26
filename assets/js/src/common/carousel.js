/**
 * Description
 */

(function ($) {
  'use strict';

  $(document).ready(function(){
    $('.ebookList__wrap').slick({
      infinite: true,
      slidesToShow: 6,
      slidesToScroll: 3,
      dots: true,
      speed: 200,
      cssEase: 'ease-in-out',
      autoplay: true,
      autoplaySpeed: 2000,
      responsive: [
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 1
          }
        }
      ]
    });
  });

})(jQuery);
