<?php
/**
 * ソーシャルにシェアするアクション
 */


/**
 * 投稿が公開されたときにつぶやく
 *
 * @param string $new_status
 * @param string $old_status
 * @param object $post
 */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	//はじめて公開にしたときだけ
	if ( ! WP_DEBUG  && ( 'publish' === $new_status ) && function_exists( 'gianism_update_twitter_status' ) ) {
		$title  = hametuha_censor( get_the_title( $post ) );
		$author = hametuha_censor( get_the_author_meta( 'display_name', $post->post_author ) );
		switch ( $old_status ) {
			case 'new':
			case 'draft':
			case 'pending':
			case 'auto-draft':
			case 'future':
				switch ( $post->post_type ) {
					case 'post':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$string = "{$author}さんが #破滅派 に新作「{$title}」を投稿しました {$url}";
						break;
					case 'announcement':
						$url = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						if ( user_can( $post->post_author, 'edit_others_posts' ) ) {
							$string = "#破滅派 編集部からのお知らせです > {$post->post_title} {$url}";
						} else {
							$author = get_the_author_meta( 'display_name', $post->post_author );
							$string = "{$author}さんから告知があります #破滅派 > {$post->post_title} {$url}";
						}
						// Slackで通知
						hametuha_slack( sprintf( '告知が公開されました: <%s|%s>', get_permalink( $post ), get_the_title( $post ) ) );
						break;
					case 'info':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$string = " #破滅派 からのお知らせ > {$post->post_title} {$url}";
						break;
					case 'thread':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$string = "{$author}さんが #破滅派 BBSにスレッドを立てました > {$title} {$url}";
						break;
					default:
						$string = false;
						break;
				}
				if ( $string ) {
					gianism_update_twitter_status( $string );
				}
				break;
		}
	}
}, 10, 3 );


/**
 * ニュースが更新されたときの通知
 *
 * @param string $new_status
 * @param string $old_status
 * @param WP_Post $post
 */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	// ニュース以外は無視
	if ( 'news' !== $post->post_type ) {
		return;
	}
	$author    = get_userdata( $post->post_author );
	$edit_link = get_edit_post_link( $post->ID, 'mail' );
	$title     = get_the_title( $post );
	$base      = [
		'title'       => $title,
		'title_link'  => $edit_link,
		'author_name' => $author->display_name,
		'author_link' => home_url( "/doujin/detail/{$author->user_nicename}/" ),
		'text'        => $post->post_excerpt,
	];
	if ( has_post_thumbnail( $post ) ) {
		$base['thumb_url'] = get_the_post_thumbnail_url( $post, 'thumbnail' );
	}
	switch ( $new_status ) {
		case 'private':
			switch ( $old_status ) {
				case 'private':
				case 'trash':
					// なにもしない
					break;
				default:
					// 没になった
					// TODO: なんらかの方法で連絡する
					hametuha_slack( '@here 公開されていたニュースがボツになりました。このニュースはもう修正できません。', [ array_merge( $base, [
						'fallback'   => sprintf( '「%s」がボツになりました。', $title ),
						'title_link' => admin_url( 'edit.php?post_type=news' ),
					] ) ], '#news' );
					break;
			}
			break;
		case 'pending':
			if ( ! user_can( $post->post_author, 'edit_others_news_posts' ) ) {
				switch ( $old_status ) {
					case 'pending':
						// 何もしない
						break;
					case 'publish':
						// 公開されていたものレビュー待ちになった
						// TODO: なんらかの方法で連絡する
						hametuha_slack( '@here 公開されていたニュースがレビュー待ちになりました。執筆者は修正してください。', [ array_merge( $base, [
							'fallback' => sprintf( '「%s」が再度レビュー待ちになりました。', $title ),
							'color'    => 'danger',
						] ) ], '#news' );
						break;
					default:
						// 承認待ちになった
						hametuha_slack( '@channel ニュースが承認待ちです。公開権限を持っている方は承認をお願いします。', [ array_merge( $base, [
							'fallback' => sprintf( '「%s」が承認待ちです。', $title ),
							'color'    => 'warning',
						] ) ], '#news' );
						break;
				}
			}
			break;
		case 'publish':
			// 投稿が公開された
			// まだpost_metaがなかったら保存
			if ( ! get_post_meta( $post->ID, '_news_published', true ) ) {
				update_post_meta( $post->ID, '_news_published', $post->post_date );
			}
			// なにかする
			switch ( $old_status ) {
				case 'publish':
				case 'private':
				case 'trash':
					// なにもしない
					break;
				default:
					// 公開された
					$string = sprintf( '#はめにゅー 更新 「%s」 %s', get_the_title( $post ), get_permalink( $post ) );
					if ( function_exists( 'gianism_update_twitter_status' ) && ! WP_DEBUG ) {
						gianism_update_twitter_status( $string );
					}
					// Slackに通知
					hametuha_slack( '@here ニュースが公開されました。', [ array_merge( $base, [
						'fallback'   => sprintf( '「%s」%s', $title, $author->display_name ),
						'title_link' => get_permalink( $post ),
						'color'      => 'good',
					] ) ], '#news' );
					break;
			}
			break;
		default:
			// それ以外はなにもしない
			break;
	}
}, 10, 3 );

// Facebookページのパーミッションを取得
add_filter( 'gianism_facebook_permissions', function( $permissions, $action ) {
	if ( false !== array_search( $action, [ 'publish', 'admin' ] ) ) {
		$permissions[] = 'publish_pages';
	}
	return $permissions;
}, 10, 2 );
