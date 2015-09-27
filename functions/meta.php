<?php
/**
 * ヘッダーなどのメタ情報に関する処理
 *
 */


/**
 * wp_titleを変更
 *
 * @param string $title
 * @param string $sep
 * @param string $seplocation
 *
 * @return string
 */
add_filter( 'wp_title', function ( $title, $sep, $seplocation ) {
	if ( is_singular( 'post' ) ) {
		global $post;
		$cats = get_the_category( $post->ID );
		if ( ! empty( $cats ) ) {
			$cat = current( $cats )->name;
		} else {
			$cat = '投稿';
		}
		$title .= "$cat {$sep} ";
	} elseif ( is_ranking() ) {
		$title = ranking_title() . " {$sep} ";
	} elseif ( is_singular( 'info' ) ) {
		$title .= "おしらせ {$sep} ";
	} elseif ( is_singular( 'faq' ) ) {
		$title .= "よくある質問 {$sep} ";
	} elseif ( is_singular( 'announcement' ) ) {
		$title .= "告知 {$sep} ";
	} elseif ( is_singular( 'anpi' ) ) {
		$title .= "安否情報 {$sep} ";
	} elseif ( is_singular( 'series' ) ) {
		$title .= "作品集 {$sep} ";
	} elseif ( is_singular( 'thread' ) ) {
		$title .= "BBS {$sep} ";
	} elseif ( is_category() ) {
		$title = "ジャンル: {$title}";
	} elseif ( is_tag() ) {
		$title = "タグ: $title";
	} elseif ( is_tax( 'faq_cat' ) ) {
		$title = "よくある質問: {$title}";
	} elseif ( is_post_type_archive( 'thread' ) ) {
		$title = "破滅派BBS {$sep} ";
	} elseif ( is_tax( 'topic' ) ) {
		$title = "破滅派BBSトピック: {$title}";
	}

	return $title;
}, 10, 3 );


/**
 * Faviconの表示
 */
function _hametuha_favicon() {
	?>
	<link rel="shortcut icon" href="<?= get_stylesheet_directory_uri(); ?>/assets/img/favicon.ico"/>
	<?php
}

add_action( 'admin_head', '_hametuha_favicon' );
add_action( 'wp_head', '_hametuha_favicon' );


/**
 * OGPのprefixを取得する
 *
 * @return string
 */
function hametuha_get_ogp_type() {
	// TODO: 投稿タイプや条件によってOGPを変更する
	if ( is_front_page() ) {
		return 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/website#';
	} else {
		return 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#';
	}
}


/**
 * JetpackのOGPを消す
 *
 * @action wp_head
 */
add_action( 'wp_head', function () {
	remove_action( 'wp_head', 'jetpack_og_tags' );
}, 1 );


/**
 * OGPを出力する
 */
add_action( 'wp_head', function () {
	//初期値を設定
	$image  = get_template_directory_uri() . '/assets/img/facebook-logo.png';
	$title  = wp_title( '|', false, 'right' ) . get_bloginfo( 'name' );
	$url    = false;
	$type   = 'article';
	$desc   = '';
	$card   = 'summary';
	$author = '';
	//個別設定
	if ( is_front_page() ) {
		$url   = trailingslashit( get_bloginfo( 'url' ) );
		$type  = 'website';
		$page  = get_post( get_option( 'page_on_front' ) );
		$desc  = $page->post_excerpt;
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $page->ID ), 'full' )[0];
	} elseif ( 'kdp' == get_query_var( 'meta_filter' ) ) {
		$url   = home_url( '/kdp/' );
		$desc  = '破滅派初の電子書籍はAmazonのKindleで入手できます。プライム会員は月1冊まで無料！';
		$image = get_stylesheet_directory_uri() . '/assets/img/jumbotron/kdp.jpg';
		$card  = 'summary_large_image';
	} elseif ( is_post_type_archive() || is_home() ) {
		$post_obj = get_post_type_object( get_query_var( 'post_type' ) ?: 'post' );
		$url      = get_post_type_archive_link( get_post_type() );
		$desc     = $post_obj->description ?: '';
		$path     = '/assets/img/jumbotron/' . get_post_type() . '.jpg';
		if ( file_exists( get_stylesheet_directory() . $path ) ) {
			$image = get_stylesheet_directory_uri() . $path;
			$card  = 'summary_large_image';
		}
	} elseif ( is_author() ) {
		global $wp_query;
		$user   = get_userdata( $wp_query->query_vars['author'] );
		$title  = $user->display_name;
		$type   = 'profile';
		$url    = get_author_posts_url( $user->ID, $user->user_nice_name );
		$image  = preg_replace( "/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar( $user->ID, 300 ) );
		$desc   = str_replace( "\n", '', get_user_meta( $user->ID, 'description', true ) );
		$author = '<meta property="profile:username" content="' . $user->user_login . '" />';
	} elseif ( is_singular() ) {
		global $post;
		$url = get_permalink();
		setup_postdata( $post );
		$desc = get_the_excerpt();
		wp_reset_postdata();
		$author = '<meta property="article:author" content="' . get_author_posts_url( $post->post_author ) . '" />';
		if ( is_singular( 'thread' ) ) {
			$image = preg_replace( "/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar( $post->post_author, 300 ) );
		} elseif ( has_post_thumbnail() ) {
			if ( $src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ) ) {
				$image = $src[0];
				$card  = 'summary_large_image';
			}
		} else {
			$images = get_posts( 'post_parent=' . get_the_ID() . '&post_mime_type=image&orderby=menu_order&order=ASC&posts_per_page=1' );
			if ( ! empty( $images ) && ( $src = wp_get_attachment_image_src( current( $images )->ID, 'large' ) ) ) {
				$image = $src[0];
			}
		}
	} elseif ( is_category() ) {
		if ( $cat = get_category( get_query_var( 'cat' ) ) ) {
			$desc = $cat->category_description;
			$url  = get_category_link( $cat );
		}
	} elseif ( is_ranking() ) {
		$url   = home_url( $_SERVER['REQUEST_URI'] );
		$image = get_stylesheet_directory_uri() . '/assets/img/jumbotron/ranking.jpg';
		$card  = 'summary_large_image';
	}
	$desc  = esc_attr( str_replace( "\n", '', $desc ) );
	$image = str_replace( '://s.', '://', $image );
	if ( ! $url ) {
		return;
	}
	echo <<<EOS
<meta name="twitter:card" content="{$card}" />
<meta name="twitter:site" content="@hametuha" />
<meta name="twitter:image" content="{$image}" />
<meta property="og:title" content="{$title}"/>
<meta property="og:url" content="{$url}" />
<meta property="og:image" content="{$image}" />
<meta property="og:description" content="{$desc}" />
<meta name="description" content="{$desc}" />
<meta property="og:type" content="{$type}" />
{$author}
<meta property="article:publisher" content="https://www.facebook.com/hametuha.inc" />
<meta property="og:site_name" content="破滅派｜オンライン文芸誌" />
<meta property="og:locale" content="ja_jp" />
<meta property="fb:app_id" content="196054397143922" />
<meta property="fb:admins" content="1034317368" />
EOS;
}, 1 );


/**
 * リッチスニペット
 */
add_action( 'wp_head', function () {
	$url  = home_url( '', 'http' );
	$name = get_bloginfo( 'name' );
	if ( is_front_page() ) {
		echo <<<HTML
<script type="application/ld+json">
{
   "@context": "http://schema.org",
   "@type": "WebSite",
   "name": "{$name}",
   "url": "{$url}",
   "potentialAction": {
     "@type": "SearchAction",
     "target": "{$url}?s={search_term_string}",
     "query-input": "required name=search_term_string"
   }
}
</script>
HTML;
	}
	$css_dir = get_template_directory_uri();
	echo <<<HTML
<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "Organization",
	"name": "破滅派",
	"url": "{$url}",
	"logo": "{$css_dir}/assets/img/hametuha-logo.png",
	"sameAs" : [
		"https://www.facebook.com/hametuha.inc",
		"https://www.twitter.com/hametuha",
		"http://plus.google.com/+HametuhaCom"
	]
}
</script>
HTML;
} );
