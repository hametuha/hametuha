<?php

/**
 * Slackでの登録情報
 */
add_filter(
	'hameslack_invite_args',
	function( $args, $user ) {
		unset( $args['last_name'] );
		$args['first_name'] = $user->user_login;
		return $args;
	},
	10,
	2
);


/**
 * ユーザー連絡にイベント参加者を追加する
 */
add_filter(
	'hamail_search_results',
	function( $results, $string ) {
		$events = get_posts(
			[
				'post_type'      => [ 'announcement', 'news' ],
				'post_status'    => [ 'publish', 'private' ],
				's'              => $string,
				'posts_per_page' => 10,
				'meta_query'     => [
					[
						'key'   => '_hametuha_commit_type',
						'value' => 1,
					],
				],
			]
		);
		if ( $events ) {
			foreach ( $events as $event ) {
				array_unshift(
					$results,
					(object) [
						'id'    => $event->ID,
						'type'  => 'participants',
						'label' => "{$event->post_title}の参加者",
						'data'  => '',
					]
				);
			}
		}
		return $results;
	},
	10,
	2
);

/**
 * イベントの参加者を取得する
 */
add_filter(
	'hamail_extra_search',
	function( $results, $type, $id ) {
		switch ( $type ) {
			case 'participants':
				if ( $post = get_post( $id ) ) {
					$event   = new \Hametuha\ThePost\Announcement( $post );
					$results = array_map(
						function ( $user ) {
							return [
								'user_id'      => $user['id'],
								'display_name' => $user['name'],
								'user_email'   => $user['mail'],
							];
						},
						$event->get_participants( true )
					);
				}
				break;
			default:
				// Do nothing
				break;
		}
		return $results;
	},
	10,
	3
);

/**
 * Replace contact form output.
 */
add_filter(
	'sp4cf7_output',
	function( $out, $post, $error ) {
		/** @var WP_Post $post */
		if ( ! $error ) {
			ob_start();
			?>
		<div class="wpcf7-post-content">
			<h3>
				「<?php echo esc_html( get_the_title( $post ) ); ?>」の作者
				<?php echo  esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?>へのお問い合わせ
			</h3>
			<p class="text-right">
				<a href="<?php echo esc_url( home_url( 'doujin/detail/' . get_the_author_meta( 'nicename', $post->post_author ) ) ); ?>" class="btn btn-outlined-secondary btn-sm">
					作者プロフィール
				</a>
			</p>
		</div>
			<?php
			$out = ob_get_contents();
			ob_end_clean();
		}
		return $out;
	},
	10,
	3
);
