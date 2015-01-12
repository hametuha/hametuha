/*!
 * 告知ページのヘルパースクリプト
 *
 *
 */
jQuery(document).ready(function($){

	'use strict';

	//TimePickerの設定
	if($('.timepicker').length > 0){
		$('.timepicker').datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm:00",
			changeMonth: true,
			changeYear: true,
			timeOnlyTitle: '時刻の指定',
			timeText: '時刻',
			hourText: '時',
			minuteText: '分',
			secondText: '秒',
			currentText: '現在',
			closeText: '閉じる',
			dayNamesMin: ['日', '月', '火', '水', '木', '金', '土'],
			monthNamesShort: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
			showMonthAfterYear: true
		});
	}
	//管理画面のラジオボタントグル
	if($('input[name=commit_type]').length > 0){
		$('input[name=commit_type]').click(function(e){
			if($(this).val() == 0){
				$('#commit-conditions').fadeOut();
			}else{
				$('#commit-conditions').fadeIn();
			}
		});
	}
	if($('input[name=commit_type]').length > 0){
		$('input[name=commit_type]').click(function(e){
			for(i = 0, l = $('input[name=commit_type]').length; i < l; i++){
				if($(this).val() != i + 1){
					$('#commit_' + (i + 1)).css('display', 'none');
				}else{
					$('#commit_' + (i + 1)).fadeIn();
				}
			}
		});
	}
	if($('input[name=commit_post_type]').length > 0){
		$('input[name=commit_post_type]').click(function(e){
			var id = 'commit_cat_' + $(this).val();
			$('p[id^=commit_cat_]').each(function(index, elt){
				if($(elt).attr('id') == id){
					$(elt).fadeIn();
				}else{
					$(elt).css('display', 'none');
				}
			});
		});
	}
	
	//Google Map用のユーティリティクラス
	var corrds = {
		
		geocoder: new google.maps.Geocoder(), 
		
		/**
		 * @return {LatLng}
		 */
		get: function(){
			var latlng = $('input[name=announcement_latlng]').val().split(':');
			if(latlng.length > 1){
				var lat = latlng[0];
				var lng = latlng[1];
				if(lat.match(/^[0-9\-\.]+$/) && lng.match(/^[0-9\-\.]+$/)){
					return new google.maps.LatLng(lat, lng);
				}else{
					return null;
				}
			}else{
				return null;
			}
		},
		
		/**
		 * @param {google.maps.LatLng}
		 * @return {undefined}
		 */
		set: function(latLng){
			$('input[name=announcement_latlng]').val(latLng.lat() + ':' + latLng.lng());
		},
		
		toLatLng: function(address, callback){
			this.geocoder.geocode( {'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					map.setCenter(results[0].geometry.location);
					marker.setPosition(results[0].geometry.location);
					corrds.set(results[0].geometry.location);
				} else {
				  callback("住所の取得に失敗しました: ", status);
				}
			});
		}
	};
	
	//Google Mapの描画（管理画面）
	if($('#announcement_map').length > 0){
		defaultLatLng = new google.maps.LatLng(35.671435, 139.720863);
		var map = new google.maps.Map(document.getElementById("announcement_map"), {
			zoom: 15,
			center: defaultLatLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		//マーカーを落とす
		var marker = new google.maps.Marker({
			position: defaultLatLng,
			map: map,
			draggable: false
		});
		var msg = function(string, result){
			alert(string + ': ' + result);
		};
		//空じゃなかったらマーカーを移動
		if($('#announcement_address').val().length > 0){
			corrds.toLatLng($('#announcement_address').val(), msg);
		}
		//ボタンをクリックしたとき、アイコンを移動する
		$('#announcement_address_search1').click(function(e){
			e.preventDefault();
			corrds.toLatLng($('#announcement_address').val(), msg);
		});
	}
	
	//Google Mapの描画（公開画面）
	if($('#gmap-announcement').length > 0){
		var address = $('#gmap-announcement').text();
		$('#gmap-announcement').text('');
		defaultLatLng = new google.maps.LatLng(35.671435, 139.720863);
		//地図を描画
		var map = new google.maps.Map(document.getElementById("gmap-announcement"), {
			zoom: 15,
			center: defaultLatLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		//マーカーを落とす
		var marker = new google.maps.Marker({
			position: defaultLatLng,
			map: map,
			draggable: false
		});
		//座標を移動
		corrds.toLatLng(address);
	}
});