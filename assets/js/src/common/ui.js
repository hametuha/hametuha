/**
 * Common UI Parts
 */

/*global Hametuha: true*/

jQuery(document).ready(function ($) {

    'use strict';

    // Off canvas
    $('[data-toggle=offcanvas]').click(function () {
        $('body').toggleClass('offcanvas-on');
    });

    // ツールチップ
    $('.help-tip').tooltip({
        trigger  : 'hover focus click',
        container: 'body'
    });

    // プロフィールページのナビ
    var profileNav = $('#profile-navi');
    if (profileNav.length) {
        var profileNavs = {};
        $('section', '#your-profile').each(function (index, section) {
            var id = 'profile-section-' + (index + 1);
            $(section).attr('id', id);
            profileNavs[id] = $(section).find('h2, h3:first-child').text();
        });
        for (var id in profileNavs) {
            if (profileNavs.hasOwnProperty(id)) {
                profileNav.append('<li><a href="#' + id + '">' + profileNavs[id] + '</a></li>');
            }
        }
    }

    $('.subnav__link--toggle').click(function(e){
        var $target = $($(this).attr('data-target'));
        e.preventDefault();
        if( $target.hasClass('toggle') ){
            $('.subnav__child').addClass('toggle');
            $target.removeClass('toggle');
        }else{
            $('.subnav__child').addClass('toggle');
        }
    });

});
