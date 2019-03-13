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

/**
 * Send notification to post author
 *
 * @todo This notification is only for post comment. Should be adopted to hamethread.
 * @param int $comment_id
 * @param \stdClass $comment_object
 */
add_action( 'wp_insert_comment', function( $comment_id, $comment_object ) {
	if ( ! $comment_object->comment_type
		&& ( $post = get_post( $comment_object->comment_post_ID ) )
		&& $comment_object->user_id && ( $post->post_author != $comment_object->user_id )
	) {
		$notifications = \Hametuha\Model\Notifications::get_instance();
		// This is comment
		$msg = trim_long_sentence( strip_tags( $comment_object->comment_content ), 80 );
		$notifications->add_comment( $post->post_author, $comment_id, sprintf( '<strong>%s</strong>に「%s」というコメントがつきました。', get_the_title( $post ), $msg ), ( $comment_object->user_id ?: $comment_object->comment_author_email ) );
		// If this is reply
		if ( $comment_object->comment_parent // これは返信であり
			&& ( $parent = get_comment( $comment_object->comment_parent ) ) // 親コメントが存在し
			&& $parent->user_id // 親コメントには通知すべきユーザーがおり
			&& $post->post_author != $parent->user_id // 投稿作成者と親コメントのユーザーが一致せず
			&& $parent->user_id != $comment_object->user_id // 投稿者と親コメント者が一緒じゃなければ
		) {
			$notifications->add_comment( $parent->user_id, $comment_id, sprintf( 'あなたのコメントに「%s」という返信がつきました。', $msg ), ( $comment_object->user_id ?: $comment_object->comment_author_email ) );
		}
	}
}, 11, 2 );

/**
 * Add notification if post is published.
 *
 * @param string $new_status
 * @param string $old_status
 * @param \WP_Post $post
 */
add_action( 'transition_post_status', function( $new_status, $old_status, \WP_Post $post ) {
	if ( 'publish' === $new_status ) {
		if ( false === array_search( $old_status, [ 'new', 'draft', 'pending', 'auto-draft', 'future' ] ) ) {
			return;
		}
		$notifications = \Hametuha\Model\Notifications::get_instance();
		switch ( $post->post_type ) {
			case 'announcement':
				$message = sprintf( '破滅派からお知らせです: <strong>%s</strong>', get_the_title( $post ) );
				$notifications->add_general( 0, $post->ID, $message, 'info@hametuha.com' );
				break;
			default:
				// Do nothing
				break;
		}
	}
}, 10, 3 );



/**
 * Add notification if review is updated.
 *
 * @param \WP_Post $post
 * @param int $user_id
 * @param array $reviewed_terms
 * @param int $rank
 */
add_action( 'hametuha_post_reviewed', function ( \WP_Post $post, $user_id = 0, $reviewed_terms = [], $rank = 0 ) {
	$count = Hametuha\Model\Review::get_instance()->get_review_count( $post->ID );
	for ( $i = 4; $i >= 0; $i -- ) {
		$step = pow( 10, $i );
		if ( $step == $count ) {
			$key = '_is_notified_' . $step;
			if ( get_post_meta( $post->ID, $key, true ) ) {
				break;
			}
			$label = sprintf( '%s件', number_format( $count ) );
			switch ( $count ) {
				case 10000:
					$subtitle = '世界はあなたのもの！';
					break;
				case 1000:
					$subtitle = '快挙達成！';
					break;
				case 100:
					$subtitle = 'ついにブレイク！';
					break;
				case 10:
					$subtitle = 'ブレイク間近！？';
					break;
				case 1:
				default:
					$subtitle = 'おめでとうございます！';
					$label    = 'はじめて';
					break;
			}
			$msg = sprintf( '%s<strong>%s</strong>に%sのレビューがつきました！', $subtitle, get_the_title( $post ), $label );
			\Hametuha\Model\Notifications::get_instance()->add_review( $post->post_author, $post->ID, $msg, $user_id );
			update_post_meta( $post->ID, $key, true );
			break;
		}
	}
}, 10, 4 );

/**
 * Register cron task for daily notification
 */
add_action( 'init', function() {
	$cron_action = 'hametuha_daily_notification';
	if ( ! wp_next_scheduled( $cron_action ) ) {
		$time = date_i18n( 'Y-m-dT11:00:00+09:00', current_time( 'timestamp' ) + 60 * 60 * 24 );
		wp_schedule_event( strtotime( $time ), 'daily', $cron_action );
	}
	add_action( $cron_action, function() {
		switch ( date_i18n( 'N' ) ) {
			case '5': // 金曜日（週間ランキング発表）
				$message = '【お知らせ】おめでとうございます。%s付の週間ランキングで%d位になりました。';
				list( $year, $month, $day ) = explode( '/', get_latest_ranking_day( 'Y/m/d' ) );
				$query = new \WP_Query( [
					'ranking'        => 'weekly',
					'year'           => $year,
					'monthnum'       => $month,
					'day'            => $day,
					'posts_per_page' => 3,
				] );
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						\Hametuha\Model\Notifications::get_instance()->add_general(
							get_the_author_meta( 'ID' ),
							get_the_ID(),
							sprintf( $message, get_latest_ranking_day( get_option( 'date_format' ) ), get_the_ranking() ),
							'info@hametuha.com'
						);
					}
				}
				break;
			default:
				// なにもしない
				break;
		}
	} );
} );
