/**
 * レスポンシブヘルパー
 * テーブルなどの要素をレスポンシブ対応にする
 */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	// .post-content内の直下のテーブルを.table-wrapperでラップ
	$( '.post-content > table' ).each( function () {
		// すでにラップされている場合はスキップ
		if ( ! $( this ).parent().hasClass( 'table-wrapper' ) ) {
			$( this ).wrap( '<div class="table-wrapper"></div>' );
		}
	} );
} );
