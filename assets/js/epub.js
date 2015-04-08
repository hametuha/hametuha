/**
 * Description
 */

/*global hoge: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){
        $(document).find('*').andSelf().contents().not('[nodeType=1]').each(function(index, txt){

        });
    });


})(jQuery);
