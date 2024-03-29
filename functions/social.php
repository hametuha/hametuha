<?php

/**
 * フッターにJS SDKを読み込む
 */
add_action( 'admin_footer', function () {
	$fb_app_id = '196054397143922';
	$fb_sdk    = 'https://connect.facebook.net/en_US/sdk.js';
	echo <<<HTML
<div id="fb-root"></div>
<script>
 window.fbAsyncInit = function() {
    FB.init({
      appId            : '{$fb_app_id}',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v3.2'
    });
  };
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "{$fb_sdk}";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
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
HTML;
} );

/**
 * Mark as this page needs chat plugin.
 */
add_action( 'wp_footer', function() {
	wp_localize_script( 'hametuha-social', 'HametuhaSocial', [
		'needChat' => ( is_page( 'help' ) || is_post_type_archive( 'faq' ) || is_singular( 'faq' ) || is_tax( 'faq_cat' ) ),
	] );
}, 1 );

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
 * Google Adsenseを出力する
 *
 * @param int|string $unit_no
 *
 */
function google_adsense( $unit_no = 1 ) {
	switch ( $unit_no ) {
		case 'after_title':
		case 1:
			echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- はめにゅータイトル直下 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="9464744841"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
HTML;
			break;
		case 2:
		case 'after_content':
			echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- はめにゅー記事下 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="3418211243"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
HTML;
			break;
		case 3:
		case 'sidebar':
			echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- はめにゅーサイドバー -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="2999408842"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
HTML;
			break;
		case 4:
		case 'archive_top':
			echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- はめにゅーアーカイブ上 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="4446972448"
     data-ad-format="auto"></ins>
<script>
 (adsbygoogle = window.adsbygoogle || []).push({});
</script>
HTML;
			break;
		case 5:
		case 'archive_bottom':
			echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- はめにゅーアーカイブ下 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="5923705643"
     data-ad-format="auto"></ins>
<script>
 (adsbygoogle = window.adsbygoogle || []).push({});
</script>
HTML;
			break;
		case 6:
		case 'related':
			if ( function_exists( 'get_the_related_post_patch' ) ) {
				$result = get_the_related_post_patch();
				if ( $result ) {
					echo $result;
				}
			}
			break;
	}
}

/**
 * 管理画面に投稿する
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
				 data-href="<?php echo hametuha_user_link( get_permalink( $post ), 'share-dashboard', 'Facebook' ); ?>"
				 data-layout="button_count"></div>
			<a href="https://twitter.com/share" class="twitter-share-button"
			   data-url="<?php echo hametuha_user_link( get_permalink( $post ), 'share-dashboard', 'Twitter' ); ?>"
			   data-text="<?php echo get_the_title( $post ); ?>" data-via="hametuha" data-hashtags="破滅派">Tweet</a>

		</div>
		<?php
	}
} );

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
 *
 * @return bool
 */
function hametuha_slack( $content, $attachment = [], $channel = '#general' ) {
	do_action( 'hameslack', $content, $attachment, $channel );

	return true;
}

/**
 * パーミッションを変更する
 */
add_filter( 'gianism_facebook_permissions', function( array $permissions, $context ) {
	switch ( $context ) {
		case 'admin':
			$permissions[] = 'publish_pages';
			break;
		default:
			// Do nothing.
			break;
	}
	return $permissions;
}, 10, 2 );

/**
 * ページ用のアクセストークンを利用する
 *
 * @return string|WP_Error
 */
function minico_access_token() {
	$response = minico_fb_request( '', 'GET', [
		'fields' => 'access_token',
	] );
	if ( is_wp_error( $response ) ) {
		return $response;
	} else {
		$access_token = $response['access_token'];
		update_option( 'minico_fb_access_token', $access_token, false );
		return (string) $access_token;
	}
}

/**
 * Facebookページのリクエスト
 *
 * @param string $endpoint
 * @param string $method
 * @param array $params
 *
 * @return array|WP_Error
 */
function minico_fb_request( $endpoint, $method = 'GET', $params = [] ) {
	try {
		if ( ! function_exists( 'gianism_fb_admin' ) ) {
			return new WP_Error( 'no_gianism', 'Gianismが有効化されていません。' );
		}
		// Get admin object
		$fb = gianism_fb_admin();
		if ( is_wp_error( $fb ) ) {
			return $fb;
		}
		// Let's get Page setting.
		$page_id  = gianism_fb_admin_id();
		$endpoint = ltrim( $endpoint, '/' );
		return $fb->api( "{$page_id}/{$endpoint}", $method, $params );
	} catch ( FacebookApiException $e ) {
		return new WP_Error( $e->getCode(), $e->getMessage() );
	}
}

/**
 * Facebookページに投稿をシェアする
 *
 * @todo 作りかけ
 * @see https://developers.facebook.com/docs/graph-api/reference/v2.7/page/feed
 * @param array $params link or string is required.
 *
 * @return stdClass|WP_Error
 */
function minico_share( $params ) {
	return minico_fb_request( 'feed', 'POST', $params );
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
			<a href="#" data-follower-id="<?php echo $author_id; ?>" class="btn btn-primary btn-follow<?php echo $class_name; ?>"
			   rel="nofollow">
				<span class="remove">フォロー中</span>
				<span class="add">
					<i class="icon-user-plus2"></i> フォロー
				</span>
				<span class="loading">
					<i class="icon-spinner2 rotation"></i> 通信中……
				</span>
			</a>
		<?php else : ?>
			<a class="btn btn-primary" href="<?php echo home_url( '/doujin/follower/', 'https' ); ?>"><i class="icon-user"></i>
				フォロワー確認</a>
			<?php
		endif;
	else :
		?>
		<a class="btn btn-primary" href="<?php echo wp_login_url( $_SERVER['REQUEST_URI'] ); ?>" rel="nofollow">フォローする</a>
		<?php
	endif;
}

/**
 * 破滅派がシェアするメッセージ内容
 *
 * @param WP_Post|int|null $post  Post object.
 * @param int              $limit Limit of message.
 * @return string
 */
function hametuha_social_share_message( $post = null, $limit = 120 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$title       = hametuha_censor( get_the_title( $post ) );
	$author      = hametuha_censor( get_the_author_meta( 'display_name', $post->post_author ) );
	$screen_name = get_user_meta( $post->post_author, 'twitter', true );
	$screen_name = $screen_name ? " @{$screen_name} " : '';
	$taxonomies  = [];
	switch ( $post->post_type ) {
		case 'post':
			// 投稿の状態をチェック
			if ( ! hametuha_is_valid_post( $post ) ) {
				return '';
			}
			$url        = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
			$string     = "{$author}さん{$screen_name}が #破滅派 に新作「{$title}」を投稿しました！\n{$url}\n#文学";
			$taxonomies = [ 'category', 'post_tag' ];
			break;
		case 'announcement':
			$url = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
			if ( user_can( $post->post_author, 'edit_others_posts' ) ) {
				$string = "#破滅派 編集部からのお知らせです > {$post->post_title}\n{$url}";
			} else {
				$author = get_the_author_meta( 'display_name', $post->post_author );
				$string = "{$author}さん{$screen_name}から告知があります #破滅派 > {$post->post_title} {$url}";
			}
			// Slackで通知
			hametuha_slack( sprintf( '告知が公開されました: <%s|%s>', get_permalink( $post ), get_the_title( $post ) ) );
			break;
		case 'info':
			$url    = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
			$string = " #破滅派 からのお知らせ > {$post->post_title}\n{$url}";
			break;
		case 'thread':
			$url        = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
			$string     = "{$author}さん{$screen_name}が #破滅派 掲示板にスレッドを立てました > 「{$title}」\n{$url}";
			$taxonomies = [ 'faq_cat' ];
			break;
		case 'faq':
			$url        = hametuha_user_link( get_permalink( $post ), 'share-auto', 'Twitter', 1 );
			$string     = "よくある質問が公開されました　> {$title}\n{$url}\n";
			$taxonomies = [ 'faq_cat' ];
			break;
		case 'news':
			$string     = sprintf( "%s\n%s\n#はめにゅー", get_the_title( $post ), get_permalink( $post ) );
			$taxonomies = [ 'nouns', 'genre' ];
			break;
		default:
			$string = '';
			break;
	}
	if ( ! $string ) {
		return $string;
	}
	// Add taxonomies as many as possible.
	foreach ( hametuha_terms_to_hashtag( $taxonomies, $post ) as $term ) {
		if ( $limit > mb_strlen( $string ) + mb_strlen( $term ) ) {
			$string .= ' ' . $term;
		}
	}
	return $string;
}
