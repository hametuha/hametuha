<?php
/**
 * スレッド関連の処理
 *
 * @package hametuha
 */

/**
 * Change thread setting.
 */
add_filter( 'hamethread_post_setting', function( $args ) {
	$args['description'] = '破滅派BBSは参加者達が意見交換をする場所です。積極的にご参加ください。匿名での投稿もできます。';
	return $args;
} );

add_filter( 'private_title_format', function() {
	return '%s';
} );

/**
 * Allow private thread.
 */
add_filter( 'hamethread_user_can_start_private_thread', function( $allow ) {
	return true;
} );

/**
 * Add editor notification.
 *
 *
 */
add_action( 'hamethread_after_thread_form', function( $args, $default ) {
	if ( $args['post'] ) {
	    return;
    }
	?>
	<div class="form-check">
		<input class="form-check-input" type="checkbox" value="1" name="notify_to_editor" id="notify_to_editor" aria-describedby="notify_to_editor_description" />
		<label class="form-check-label" for="notify_to_editor">
			編集者に知らせる
		</label>
		<small id="notify_to_editor_description" class="form-text text-muted">
            気づいてほしい場合は通知を送ってください。
		</small>
	</div>
	<?php
}, 10, 2 );

/**
 * Add post arguments.
 */
add_filter( 'hamethread_new_thread_post_params', function( $args ) {
    $args['notify_to_editor'] = [
        'type'        => 'int',
        'description' => 'Flag to detect send notification to editor.',
        'default'     => 0,
        'validate_callback' => function( $var ) {
            return is_numeric( $var );
        },
    ];
    return $args;
} );

/**
 * Send notification to editor.
 *
 */
add_action( 'hamethread_new_thread_inserted', function( $post_id, WP_REST_Request $request ) {
    if ( ! $request->get_param( 'notify_to_editor' ) ) {
        return;
    }
    $post = get_post( $post_id );
    // Send slack notification.
    do_action( 'hameslack', '掲示板に書き込みがありました。', [ [
		'fallback'    => get_the_title( $post ),
		'title'       => get_the_title( $post ),
		'title_link'  => get_permalink( $post ),
		'author_link' => get_permalink( $post ),
		'author_name' => get_the_author_meta( 'display_name', $post->post_author ),
		'color'       => '#E80000',
        'text'        => '編集者に見て欲しいそうです。',
    ] ], '#admin' );
}, 10, 2 );