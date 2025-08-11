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
	<div class="form-check">
		<input class="form-check-input" type="checkbox" value="1" name="post_as_anonymous" id="post_as_anonymous" aria-describedby="post_as_anonymous_description" />
		<label class="form-check-label" for="post_as_anonymous">
			匿名で投稿する
		</label>
		<small id="post_as_anonymous_description" class="form-text text-muted">
			匿名で投稿すると、あとから編集できません。
		</small>
	</div>
	<?php
}, 10, 2 );

/**
 * Validation for private && anonymous
 */
add_filter( 'hamethread_new_thread_validation', function( WP_Error $error, WP_REST_Request $request ) {
	if ( $request->get_param( 'is_private' ) && $request->get_param( 'post_as_anonymous' ) ) {
		$error->add( 'hidden_thread', '匿名かつ非公開だと誰も見られないスレッドになってしまいます。', [
			'response' => 400,
			'status'   => 400,
		] );
	}
	return $error;
}, 10, 2 );

/**
 * Add post arguments.
 */
add_filter( 'hamethread_new_thread_post_params', function( $args ) {
	$args['notify_to_editor']  = [
		'type'              => 'int',
		'description'       => 'Flag to detect send notification to editor.',
		'default'           => 0,
		'validate_callback' => function( $var ) {
			return is_numeric( $var );
		},
	];
	$args['post_as_anonymous'] = [
		'type'              => 'int',
		'description'       => 'Flag to detect post as anonymous.',
		'default'           => 0,
		'validate_callback' => function( $var ) {
			return is_numeric( $var );
		},
	];
	return $args;
} );

/**
 * Allow anonymous post.
 */
add_filter( 'hamethread_new_thread_post_arg', function( array $args, WP_REST_Request $request ) {
	if ( $request->get_param( 'post_as_anonymous' ) && ( $anonymous = hametuha_get_anonymous_user() ) ) {
		$args['post_author'] = $anonymous->ID;
	}
	return $args;
}, 10, 2 );

/**
 * Remove current user from watch list.
 */
add_filter( 'hamethread_default_subscribers', function( array $subscribers, $post_id, $user_id, WP_REST_Request $request ) {
	if ( $request->get_param( 'post_as_anonymous' ) ) {
		$filtered = [];
		foreach ( $subscribers as $subscriber ) {
			if ( $subscriber != $user_id ) {
				$filtered[] = $subscriber;
			}
		}
		$subscribers = $filtered;
	}
	return $subscribers;
}, 10, 4 );

/**
 * Anonymous and notification staff
 */
add_action( 'hamethread_new_thread_inserted', function( $post_id, WP_REST_Request $request ) {
	$post = get_post( $post_id );
	// If anonymous, mark ID.
	if ( $request->get_param( 'post_as_anonymous' ) && ( $anonymous = hametuha_get_anonymous_user() ) && $post->post_author == $anonymous->ID ) {
		update_post_meta( $post->ID, '_anonymous_author', get_current_user_id() );
	}
	// Send slack notification.
	if ( $request->get_param( 'notify_to_editor' ) ) {
		do_action( 'hameslack', '掲示板に書き込みがありました。', [
			[
				'fallback'    => get_the_title( $post ),
				'title'       => get_the_title( $post ),
				'title_link'  => get_permalink( $post ),
				'author_link' => get_permalink( $post ),
				'author_name' => get_the_author_meta( 'display_name', $post->post_author ),
				'color'       => '#E80000',
				'text'        => '編集者に見て欲しいそうです。',
			],
		], '#admin' );
	}
}, 10, 2 );

/**
 * Allow anonymous comment.
 */
add_filter( 'hamethread_new_comment_rest_args', function( $args, $http_method ) {
	if ( 'POST' === $http_method ) {
		$args['post_as_anonymous'] = [
			'type'              => 'int',
			'description'       => 'Flag to detect post as anonymous.',
			'default'           => 0,
			'validate_callback' => function( $var ) {
				return is_numeric( $var );
			},
		];
	}
	return $args;
}, 10, 2 );

/**
 * Display anonymous comment toggle.
 */
add_action( 'hamethread_after_comment_form', function( $args, $comment = null ) {
	if ( $comment ) {
		return;
	}
	?>
	<div class="form-check">
		<input class="form-check-input" type="checkbox" value="1" name="post_as_anonymous" id="post_as_anonymous" aria-describedby="post_as_anonymous_description" />
		<label class="form-check-label" for="post_as_anonymous">
			匿名でコメントする
		</label>
		<small id="post_as_anonymous_description" class="form-text text-muted">
			匿名でコメントした場合、スレッド主および他の閲覧者には名前が表示されません。
			過度に攻撃的な内容にならないよう注意してください。
		</small>
	</div>
	<?php
}, 10, 2 );

/**
 * Display floating button.
 */
add_action( 'hametuha_after_whole_body', function() {
	if ( ! ( is_singular( 'thread' ) || is_post_type_archive( 'thread' ) || is_tax( 'topic' ) ) ) {
		return;
	}
	if ( ! function_exists( 'hamethread_user_can_start' ) ) {
		return;
	}
	if ( hamethread_user_can_start() ) :
		?>
	<footer class="hamethread-footer text-center">
		<button data-hamethread="create" class="btn btn-lg btn-danger" data-parent="0" data-private="0">
			<i class="fas fa-folder-plus"></i> スレッドを開始する
		</button>
	</footer>
		<?php
	endif;
} );

/**
 * Post comment.
 */
add_filter( 'hamethread_new_comment_params', function( array $params, WP_REST_Request $request ) {
	if ( $request->get_param( 'post_as_anonymous' ) && ( $anonymous = hametuha_get_anonymous_user() ) ) {
		$params = array_merge( $params, [
			'comment_author'       => $anonymous->display_name,
			'comment_author_email' => $anonymous->user_email,
			'comment_author_url'   => $anonymous->user_url,
			'user_id'              => $anonymous->ID,
		] );
	}
	return $params;
}, 10, 2 );

/**
 * Save original author comment.
 */
add_action( 'hamethread_new_comment_inserted', function( $comment_id, array $comment_param, WP_REST_Request $request ) {
	if ( $request->get_param( 'post_as_anonymous' ) && ( $anonymous = hametuha_get_anonymous_user() ) ) {
		update_comment_meta( $comment_id, '_anonymous_author', get_current_user_id() );
	}
}, 10, 3 );

/**
 * Remove anonymous user from subscribers.
 */
add_filter( 'hamethread_subscribers', function( $subscribers, $post ) {
	if ( $anonymous = hametuha_get_anonymous_user() ) {
		$subscribers = array_filter( $subscribers, function( $subscriber ) use ( $anonymous ) {
			return $subscriber != $anonymous->ID;
		} );
	}
	return $subscribers;
}, 10, 2 );

/**
 * Hide comment author name if removed.
 *
 * @param string     $author
 * @param int        $comment_id
 * @param WP_Comment $comment
 * @return string
 */
add_filter( 'get_comment_author', function ( $author, $comment_id, $comment ) {
	if ( hametuha_is_deleted_users_comment( $comment ) ) {
		$author = __( '退会したユーザー', 'hametuha' );
	}
	return $author;
}, 10, 3 );

/**
 * Hide user comment.
 *
 * @param string     $comment_text
 * @param WP_Comment $comment
 */
add_filter( 'get_comment_text', function( $comment_text, $comment ) {
	if ( hametuha_is_deleted_users_comment( $comment ) && ! current_user_can( 'edit_post', $comment->comment_post_ID ) ) {
		$comment_text = <<<HTML
退会したユーザーのコメントは表示されません。
<small>※管理者と投稿者には表示されます。</small>
HTML;
	}
	return $comment_text;
}, 10, 2 );

/**
 * Comments can only editable by editors.
 */
add_filter( 'map_meta_cap', function( $caps, $cap, $user_id, $args ) {
	if ( 'edit_comment' === $cap ) {
		$caps = [ 'edit_others_posts' ];
	}
	return $caps;
}, 10, 4 );
