<?php

/**
 * デフォルトのコメント以外が表示されないようにする
 *
 * @param WP_Comment_Query $comment_query
 */
add_action( 'pre_get_comments', function( &$comment_query ) {
	if ( ! $comment_query->query_vars['type'] ) {
		// コメントのタイプが指定されていなかったら、excludeを設定
		$exclude = (array) $comment_query->query_vars['type__not_in'];
		$exclude = array_merge( array_filter( $exclude ),  [ 'participant', 'review' ] );
		$comment_query->query_vars['type__not_in'] = $exclude;
	}
} );

/**
 * Send mail
 */
add_action( 'hametuha_notification', function( $template, $subject, $to, $data = [] ) {
	if ( ! ( $body = hameplate( "templates/mail/{$template}", '', $data, false ) ) ) {
		return;
	}
	wp_mail( $to, $subject, $body );
}, 10, 4 );
