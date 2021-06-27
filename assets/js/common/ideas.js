/*!
 * アイデアを表示するボタン
 *
 * @handle hametuha-idea-button
 * @deps jquery, angular, wp-api
 */

const $ = jQuery;


// ストックボタン
$( document ).on( 'click', 'a[data-stock]', function ( e ) {
	const $button = $( this ),
		post_id = $button.attr( 'data-stock' );
	e.preventDefault();
	$button.attr( 'disabled', true );
	$.ajax( {
		url: wpApiSettings.root + 'hametuha/v1/idea/' + post_id + '/',
		method: 'POST',
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
		}
	} ).done( function ( response ) {
		$button.attr( 'data-stock', null ).text( 'ストック済み' );
		Hametuha.alert( 'このアイデアをストックしました。' );
	} ).fail( function ( response ) {
		const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
		Hametuha.alert( message, true );
		$button.attr( 'disabled', false );
	} );
} );

// 薦めるボタン
$( document ).on( 'click', 'a[data-recommend]', function ( e ) {
	const $button = $( this ),
		ideaId = $button.attr( 'data-recommend' );
	e.preventDefault();
	Hametuha.modal.open( 'アイデアを薦める', function ( $box ) {
		// フォームを取得して表示する
		$.post( $button.attr( 'href' ) ).done( function ( response ) {
			$box.removeClass( 'loading' ).find( '.modal-body' ).append( response.html );
		} ).fail( function ( response ) {
			const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
			Hametuha.alert( message, true );
			Hametuha.modal.close();
		} );
	} );
} );

// 薦めるフォーム
$( document ).on( 'submit', '#recommend-idea-form', function ( e ) {
	e.preventDefault();
	$.ajax( {
		method: 'PUT',
		url: wpApiSettings.root + 'hametuha/v1/idea/' + $( this ).attr( 'data-post-id' ) + '/',
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
		},
		data: {
			user_id: $( this ).find( "#recommend_to" ).val()
		}
	} ).done( function ( response ) {
		Hametuha.alert( response.message );
		Hametuha.modal.close();
	} ).fail( function ( response ) {
		const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
		Hametuha.alert( message, true );
	} );
} );

// アイデアを投稿する
$( document ).on( 'click', 'a[data-action="post-idea"]', function ( e ) {
	e.preventDefault();
	const $button = $( this );
	Hametuha.modal.open( 'アイデアを投稿する', function ( $box ) {
		$.post( $button.attr( 'href' ) ).done( function ( response ) {
			$box.removeClass( 'loading' ).find( '.modal-body' ).append( response.html );
		} ).fail( function ( response ) {
			const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
			Hametuha.alert( message, true );
			Hametuha.modal.close();
		} );
	} );
} );

// アイデアを保存する
$( document ).on( 'submit', '#new-idea-form', function ( e ) {
	e.preventDefault();
	let endpoint = 'hametuha/v1/idea/mine/',
		$idContainer = $( this ).find( '#new-idea-id' ),
		method, data;
	data = {
		title: $( '#new-idea-name' ).val(),
		content: $( '#new-idea-content' ).val(),
		status: $( '#new-idea-privacy' ).attr( 'checked' ) ? 'private' : 'publish',
		genre: $( '#new-idea-genre' ).val()
	};
	if ( $idContainer.length ) {
		method = 'PUT';
		data.post_id = $idContainer.val();
	} else {
		method = 'POST';
	}
	$.ajax( {
		method: method,
		url: wpApiSettings.root + endpoint,
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
		},
		data: data
	} ).done( function ( response ) {
		Hametuha.modal.close();
		if ( window.location.href === response.url ) {
			Hametuha.alert( response.message + '3秒後にページを更新します……' );
			setTimeout( function () {
				window.location.reload();
			}, 3000 );
		} else {
			Hametuha.alert( response.message + '<a class="alert-link" href="' + response.url + '">アイデアのページヘ移動する</a>' );
		}
	} ).fail( function ( response ) {
		const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
		Hametuha.alert( message, true );
	} );
} );

// アイデアを削除する
$( document ).on( 'click', 'a[data-action="delete-idea"]', function ( e ) {
	e.preventDefault();
	const postId = $( this ).attr( 'data-post-id' );
	Hametuha.confirm( 'このアイデアを削除してよろしいですか？', function () {
		$.ajax( {
			method: 'DELETE',
			url: wpApiSettings.root + 'hametuha/v1/idea/mine/?post_id=' + postId,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			}
		} ).done( function ( response ) {
			Hametuha.alert( response.message );
		} ).fail( function ( response ) {
			const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
			Hametuha.alert( message, 'danger' );
		} );
	}, true );
} );

// アイデアを編集する
$( document ).on( 'click', 'a[data-action="edit-idea"]', function ( e ) {
	const endpoint = $( this ).attr( 'href' );
	e.preventDefault();
	Hametuha.modal.open( 'アイデアを編集する', function ( $box ) {
		$.post( endpoint ).done( function ( response ) {
			$box.removeClass( 'loading' ).find( '.modal-body' ).append( response.html );
		} ).fail( function ( response ) {
			const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
			Hametuha.alert( message, 'danger' );
			Hametuha.modal.close();
		} );
	} );
} );
