/*!
 * ログイン名変更
 *
 * @handle hametuha-login-changer
 * @deps jquery-form,hametuha-common
 */

/*global Hametuha: true*/

const $ = jQuery;

$( document ).ready( function () {
	const form = $( '#change-login-form' );
	const input = $( '#login_name' );
	const container = input.parents( '.has-feedback' );
	const submit = form.find( 'input[type=submit]' );

	if ( form.length ) {
		let timer = null;
		const checkLogin = function ( callback ) {
			// リセットする
			if ( timer ) {
				clearTimeout( timer );
			}
			timer = setTimeout( function () {
				container.addClass( 'loading' );
				container.removeClass( 'has-success' ).removeClass( 'has-error' );
				$.ajax( input.attr( 'data-check' ), {
					type: 'GET',
					dataType: 'json',
					data: {
						login: input.val(),
						_wpnonce: form.find( 'input[name=_wpnonce]' ).val()
					},
					success: function ( result ) {
						if ( result.success ) {
							container.addClass( 'has-success' );
							$( '#login_nicename' ).val( result.niceName );
							submit.prop( 'disabled', false );
							if ( callback ) {
								callback();
							}
						} else {
							container.addClass( 'has-error' );
							submit.prop( 'disabled', true );
						}
					},
					error: function () {
						container.addClass( 'has-error' );
						submit.prop( 'disabled', true );
					},
					complete: function () {
						container.removeClass( 'loading' );
					}
				} );
			}, 1500 );
		};

		// キータイプで検索
		input.keyup( function () {
			if ( $( this ).val().length ) {
				checkLogin();
			}
		} );
		// フォーカスが外れたら検索
		input.blur( function () {
			if ( $( this ).val().length ) {
				checkLogin();
			}
		} );

		// 送信
		form.submit( function ( e ) {
			e.preventDefault();
			if ( input.val().length && window.confirm( 'ログイン名を変更します。よろしいですか？' ) ) {
				checkLogin( function () {
					form.ajaxSubmit( {
						dataType: 'json',
						success: function ( result ) {
							Hametuha.alert( result.message, 'success' );
							setTimeout( function () {
								window.location.href = result.url;
							}, 5000 );
						},
						error: function () {
							Hametuha.alert( '更新に失敗しました。もう一度やり直してください。', 'error' );
						}
					} );
				} );
			}
		} );
	}


	// プロフィール写真変更フォーム
	$( '#select-picture-form, #delete-picture-form' ).submit( function ( e ) {
		const $checked = $( 'input:checked', '#pic-file-list' );
		if ( !$checked.length ) {
			e.preventDefault();
			Hametuha.alert( '画像が選択されていません。' );
		} else {
			$( this ).find( '.attachment_id_holder' ).val( $checked.val() );
		}
	} );

} );
