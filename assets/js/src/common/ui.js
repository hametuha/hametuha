/**
 * Common UI Parts
 */

/*global Hametuha: true*/

jQuery(document).ready(function ($) {

    'use strict';

    // mmenu
    $("nav#header-navigation").mmenu({
        iconPanels: true,
        navbars: [
            true, // trueを渡すと普通のタイトルバーが出る
            {
                position: 'top',
                content: [
                    '<form class="mm-search" method="get" action="/"><input name="s" type="search" placeholder="サイト内を検索" /> </form>'
                ]
            },
        ]
    }, {
        offCanvas: {
            pageNodetype: '#whole-body'
        }
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

    $('.write-panel-btn').click(function(e) {
        e.preventDefault();
        $('#write-panel').toggleClass('open');
    } );

});
