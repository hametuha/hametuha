/**
 * Description
 */

/*global google: true*/
/*global Hametuha: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){
        var map = $('#gmap-announcement');
        if( map.length ){
            var address = map.attr('data-address'),
                gmap = new google.maps.Map(map.get(0), {
                    center: new google.maps.LatLng(35.672036, 139.74223),
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }),
                geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                address: address
            }, function(results, status){
                if ( status == google.maps.GeocoderStatus.OK ) {
                    var center = results[0].geometry.location,
                        marker_image = new google.maps.MarkerImage(HametuhaAnnouncement.icon);
                    marker_image.scaledSize = new google.maps.Size(64, 64);

                    var marker = new google.maps.Marker({
                        position: center,
                        map: gmap,
                        draggable: false,
                        icon: marker_image
                    });
                    gmap.setCenter(center);
                } else {
                    map.remove();
                    Hametuha.alert("住所の取得に失敗しました。地図を表示できません。ごめんなさい。", true);
                }
            });
        }

    });
})(jQuery);
