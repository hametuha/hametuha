<?php

/**
 * フッターにJS SDKを読み込む
 */
add_action( 'admin_footer', function () {
	echo <<<HTML
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.4&appId=196054397143922";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
HTML;
} );


/**
 * 短いURLを取得する
 *
 * @param string $url
 * @param array $args
 *
 * @return bool|string
 */
function hametuha_short_links( $url, $args = [] ) {
	$links = \Hametuha\Model\ShortLinks::get_instance();

	return $links->get_shorten( add_query_arg( $args, $url ) );
}

/**
 * キャンペーンを開始する
 *
 * @param string $url
 * @param string $label
 * @param string $source
 * @param null|int $author
 *
 * @return mixed
 */
function hametuha_user_link( $url, $label, $source, $author = null ) {
	if ( is_null( $author ) ) {
		$author = get_current_user_id();
	}

	return hametuha_short_links( $url, [
		'utm_source'   => $source,
		'utm_campaign' => $label,
		'utm_medium'   => $author,
	] );
}

/**
 * 一連のシェアボタンを出力する
 *
 * @param WP_Post $post 指定しない場合は現在の投稿
 *
 * @return array
 */
function hametuha_share( $post = null ) {
	if ( is_singular() ) {
		$post    = get_post( $post );
		$title   = get_the_title( $post );
		$url     = get_permalink( $post );
		$post_id = $post->ID;
	} else {
		$title   = get_bloginfo( 'name' );
		$url     = home_url( '/' );
		$post_id = 0;
	}
	$encoded_url   = rawurlencode( $url );
	$encoded_title = rawurlencode( $title . ' | 破滅派' );
	$hash_tag      = rawurlencode( '#破滅派' );
	$data_title    = esc_attr( $title );
	$links         = [];
	foreach (
		[
			[
				'facebook',
				hametuha_user_link( $url, 'share-single', 'Facebook' ),
				'4',
				true,
			    'シェア',
			],
			[
				'twitter',
				'https://twitter.com/share?url=' . rawurlencode( hametuha_user_link( $url, 'share-single', 'Twitter ' ) ) . '&amp;text='.rawurlencode( $title )."%20{$hash_tag}",
				'3',
				false,
				'呟く',
			],
			[
				'googleplus',
				'https://plus.google.com/share?url=' . rawurlencode( hametuha_user_link( $url, 'share-single', 'Google+' ) ),
				'4',
				true,
				'シェア',
			],
			[
				'hatena',
				"http://b.hatena.ne.jp/add?title={$encoded_title}&amp;url={$encoded_url}",
				'',
				true,
				'はてぶ',
			],
			[
				'line',
				"line://msg/text/{$encoded_title}%20" . rawurlencode( hametuha_user_link( $url, 'share-single', 'Line' ) ),
				'',
				false,
				'送る',
			],
		] as list( $brand, $href, $suffix, $blank, $text )
	) {
		if ( 'hatena' === $brand ) {
			$additional_class = ' hatena-bookmark-button';
			$data             = ' data-hatena-bookmark-layout="simple"';
		} else {
			$data             = '';
			$additional_class = '';
		}
		$links[ $brand ] = sprintf(
			'<a class="share share--%1$s share-retrieve%8$s" data-medium="%1$s" data-target="%2$d" href="%3$s"%5$s%7$s>
				<i class="icon-%1$s%4$s"></i>
				<span>%6$s</span>
			</a>',
			$brand,
			$post_id,
			$href,
			$suffix,
			( $blank ? ' target="_blank"' : '' ),
			$text,
			$data,
			$additional_class
		);
	}

	return $links;
}

/**
 * Google Adsenseを出力する
 *
 * @param int $unit_no
 *
 * @deprecated
 */
function google_adsense( $unit_no = 1 ) {
	switch ( $unit_no ) {
		default:
			echo <<<EOS
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 破滅派記事直後 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="4859005648"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
EOS;
			break;
	}
}

/**
 * テスト
 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( 'post' == $screen->base && 'post' == $screen->post_type ) {
		global $post;
		if ( 'publish' != $post->post_status ) {
			return;
		}
		?>
			<div class="admin-notice admin-notice--info">
				<p>
					<span class="dashicons dashicons-info"></span> この投稿は<strong>公開済み</strong>です。
					投稿を宣伝してみんなに読んでもらいましょう。
				</p>
				<div class="fb-share-button"
				     data-href="<?= hametuha_user_link( get_permalink( $post ), 'share-dashboard', 'Facebook' ) ?>"
				     data-layout="button_count"></div>
				<a href="https://twitter.com/share" class="twitter-share-button"
				   data-url="<?= hametuha_user_link( get_permalink( $post ), 'share-dashboard', 'Twitter' ) ?>"
				   data-text="<?= get_the_title( $post ) ?>" data-via="hametuha" data-hashtags="破滅派">Tweet</a>
				<script>
					! function (d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
						if (!d.getElementById(id)) {
							js = d.createElement(s);
							js.id = id;
							js.src = p + '://platform.twitter.com/widgets.js';
							fjs.parentNode.insertBefore(js, fjs);
						}
					}(document, 'script', 'twitter-wjs');
				</script>
			</div>
		<?php
	}
} );

/**
 * 投稿が公開されたときにつぶやく
 *
 * @param string $new_status
 * @param string $old_status
 * @param object $post
 */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	//はじめて公開にしたときだけ
	if ( ! WP_DEBUG && 'publish' === $new_status && function_exists( 'update_twitter_status' ) ) {
		switch ( $old_status ) {
			case 'new':
			case 'draft':
			case 'pending':
			case 'auto-draft':
			case 'future':
				switch ( $post->post_type ) {
					case 'post':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$author = get_the_author_meta( 'display_name', $post->post_author );
						$string = "{$author}さんが #破滅派 に新作「{$post->post_title}」を投稿しました {$url}";
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
						$author = get_the_author_meta( 'display_name', $post->post_author );
						$string = "{$author}さんが #破滅派 BBSにスレッドを立てました > {$post->post_title} {$url}";
						break;
					case 'newsletter':
						$string = sprintf(
							'【業務連絡】メルマガ %s が送信されました。そのうち、みなさんのお手元に届きます。登録はこちらから %s',
							get_the_title( $post ),
							home_url( '/merumaga/' )
						);
						break;
					default:
						$string = false;
						break;
				}
				if ( $string ) {
					update_twitter_status( $string );
				}
				break;
		}
	}
}, 10, 3 );

/**
 * twitterのつぶやきを表示する
 *
 * @param string $url
 */
function show_twitter_status( $url ) {
	/** @var WP_Embed $wp_embed */
	global $wp_embed;
	echo $wp_embed->autoembed( $url );
}

/**
 * Slackに投稿する
 *
 * @param string $content Slackに投稿する文字列
 * @param array $attachment 添付がある場合は、連想配列を渡す
 * @param string $channel Default '#general'
 * @return bool
 */
function hametuha_slack( $content, $attachment = [], $channel = '#general' ) {
	if ( ! defined( 'SLACK_ENDPOINT' ) ) {
		return false;
	}
	$payload = [
		'channel' => $channel,
	];
	if ( WP_DEBUG ) {
		$content = "【テスト投稿】 {$content}";
	}
	$payload['text'] = $content;
	if ( $attachment ) {
		$payload['attachments'] = [ $attachment ];
	}
	$ch = curl_init();
	curl_setopt_array( $ch, [
		CURLOPT_URL => SLACK_ENDPOINT,
		CURLOPT_POST => true,
	    CURLOPT_POSTFIELDS => 'payload='.json_encode( $payload ),
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_TIMEOUT => 5,
	] );
	$result = curl_exec( $ch );
	if ( ! $result ) {
		$err = curl_error( $ch );
		$no  = curl_errno( $ch );
		error_log( sprintf( 'SLACK_ERR %s %s', $no, $err ) );
	}
	curl_close( $ch );
	return false !== $result;
}

/**
 * フォローボタンを出す
 *
 * @param int $author_id
 * @param bool $block
 */
function hametuha_follow_btn( $author_id, $block = false ) {
	static $loaded = false;
	if ( ! $loaded ) {
		wp_enqueue_script( 'hametu-follow' );
		$loaded = true;
	}
	if ( is_user_logged_in() ) :
		if ( get_current_user_id() != $author_id ) :
			$class_name = \Hametuha\Model\Follower::get_instance()->is_following( get_current_user_id(), $author_id )
				? ' btn-following'
				: '';
			if ( $block ) {
				$class_name .= ' btn-block';
			}
			?>
			<a href="#" data-follower-id="<?= $author_id ?>" class="btn btn-primary btn-follow<?= $class_name ?>"
			   rel="nofollow">
				<span class="remove">フォロー中</span>
				<span class="add">
					<i class="icon-user-plus2"></i> フォローする
				</span>
				<span class="loading">
					<i class="icon-spinner2 rotation"></i> 通信中……
				</span>
			</a>
		<?php else : ?>
			<a class="btn btn-primary" href="<?= home_url( '/doujin/follower/', 'https' ) ?>"><i class="icon-user"></i>
				フォロワー確認</a>
		<?php endif;
	else : ?>
		<a class="btn btn-primary" href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>" rel="nofollow">フォローする</a>
	<?php endif;
}
