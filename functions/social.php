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
 * シェア数カウント用のAPIを設定する
 */
add_action( 'admin_init', function () {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		foreach ( [ 'wp_ajax_', 'wp_ajax_nopriv_' ] as $action ) {
			add_action( "{$action}hametuha_share_count", '_hametuha_share_count' );
		}
	}
} );


/**
 * シェア数を取得する
 *
 * @internal
 */
function _hametuha_share_count() {
	$json = [
		'success' => false,
		'result'  => [],
	];
	try {
		$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
		$key     = "share_count_{$post_id}";
		$result  = get_transient( $key );
		if ( '0' == $result ) {
			if ( ! $post_id ) {
				$permalink = home_url( '/', 'http' );
			} else {
				if ( ! ( $post = get_post( $post_id ) ) ) {
					throw new Exception( '投稿は存在しません', 404 );
				}
				$permalink = get_permalink( $post );
			}
			$result = [];
			$url    = rawurlencode( $permalink );
			foreach (
				[
					'facebook'   => 'http://graph.facebook.com/?id=' . $url,
					'twitter'    => 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url,
					'hatena'     => 'http://api.b.st-hatena.com/entry.count?url=' . $url,
					'googleplus' => 'https://clients6.google.com/rpc?key=AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ',
					'pocket'     => 'http://widgets.getpocket.com/v1/button?v=1&count=horizontal&url=' . $url,
				] as $brand => $endpoint
			) {
				if ( 'googleplus' == $brand ) {
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $endpoint );
					curl_setopt( $ch, CURLOPT_POST, 1 );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( [
						[
							'method'     => 'pos.plusones.get',
							"id"         => "p",
							"params"     => [
								"nolog"   => true,
								"id"      => $permalink,
								"source"  => "widget",
								"userId"  => "@viewer",
								"groupId" => "@self"
							],
							"jsonrpc"    => "2.0",
							"key"        => "p",
							"apiVersion" => "v1",
						]
					] ) );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) );
					$response = curl_exec( $ch );
					curl_close( $ch );
					//JSONデータからカウント数を取得
					$obj = json_decode( $response, true );
					//カウント(データが存在しない場合は0扱い)
					if ( ! isset( $obj[0]['result']['metadata']['globalCounts']['count'] ) ) {
						$cnt = 0;
					} else {
						$cnt = $obj[0]['result']['metadata']['globalCounts']['count'];
					}
				} else {
					$response = wp_remote_get( $endpoint );
					if ( is_wp_error( $response ) ) {
						$result[ $brand ] = 'N/A';
						continue;
					}
					switch ( $brand ) {
						case 'facebook':
							$res = json_decode( $response['body'] );
							$cnt = ( isset( $res->shares ) && is_numeric( $res->shares ) ) ? intval( $res->shares ) : 0;
							break;
						case 'twitter':
							$res = json_decode( $response['body'] );
							$cnt = isset( $res->count ) ? intval( $res->count ) : 0;
							break;
						case 'hatena':
							$cnt = is_numeric( $response['body'] ) ? (int) $response['body'] : 0;
							break;
						case 'pocket':
							$cnt = 0;
							if ( preg_match( '#<em id="cnt">([0-9]+)</em>#u', $response['body'], $match ) ) {
								$cnt = (int) $match[1];
							}
							break;
						default:
							// Do nothing
							$cnt = 0;
							continue;
							break;
					}
				}
				$result[ $brand ] = $cnt;
				if ( $cnt && $post_id ) {
					update_post_meta( $post_id, "_sns_count_{$brand}", $cnt );
				}
			}
			set_transient( $key, $result, 60 * 30 );
		}
		$json['result']  = $result;
		$json['success'] = true;
	} catch ( Exception $e ) {
		$json['message'] = $e->getMessage();
	}
	wp_send_json( $json );
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
	$links         = [];
	foreach (
		[
			[
				'facebook',
				hametuha_user_link( $url, 'share-single', 'Facebook' ),
				'4',
				true,
			],
			[
				'twitter',
				'https://twitter.com/share?url=' . rawurlencode( hametuha_user_link( $url, 'share-single', 'Twitter ' ) ) . "&amp;text={$encoded_title}%20{$hash_tag}&amp;via=hametuha",
				'3',
				true,
			],
			[
				'googleplus',
				'https://plus.google.com/share?url=' . rawurlencode( hametuha_user_link( $url, 'share-single', 'Google+' ) ),
				'4',
				true,
			],
			[
				'hatena',
				"http://b.hatena.ne.jp/add?title={$encoded_title}&amp;url={$encoded_url}",
				'',
				true,
			],
			[
				'pocket',
				"http://getpocket.com/edit?url={$encoded_url}&amp;title={$encoded_title}",
				'',
				true,
			],
			[
				'line',
				"line://msg/text/{$encoded_title}%20" . rawurlencode( hametuha_user_link( $url, 'share-single', 'Line' ) ),
				'',
				false,
			],
		] as list( $brand, $href, $suffix, $blank )
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
				%6$s
			</a>',
			$brand,
			$post_id,
			$href,
			$suffix,
			( $blank ? ' target="_blank"' : '' ),
			( 'line' != $brand ? '<span>---</span>' : '<span>送る</span>' ),
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
						$string = "{$author}さんが新作「{$post->post_title}」を投稿しました {$url} #破滅派";
						break;
					case 'announcement':
						$url = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						if ( user_can( $post->post_author, 'edit_others_posts' ) ) {
							$string = "破滅派編集部からのお知らせです > {$post->post_title} {$url}";
						} else {
							$author = get_the_author_meta( 'display_name', $post->post_author );
							$string = "{$author}さんから告知があります > {$post->post_title} {$url} #破滅派";
						}
						break;
					case 'anpi':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$author = get_the_author_meta( 'display_name', $post->post_author );
						$string = "{$author}さんの安否です。「{$post->post_title}」 {$url} #破滅派";
						break;
					case 'info':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$string = "お知らせ > {$post->post_title} {$url} #破滅派";
						break;
					case 'thread':
						$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
						$author = get_the_author_meta( 'display_name', $post->post_author );
						$string = "{$author}さんがBBSにスレッドを立てました > {$post->post_title} {$url} #破滅派";
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
