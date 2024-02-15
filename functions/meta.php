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
	$sep = '｜';
	if ( is_front_page() ) {
		// フロントページ
		return implode( $sep, [ get_bloginfo( 'name' ), get_bloginfo( 'description' ) ] );
	} elseif ( is_singular( 'post' ) ) {
		// 投稿ページ
		return sprintf(
			'「%s」%s（%s, %s, %s年）',
			get_the_title( get_queried_object() ),
			hametuha_author_name( get_queried_object() ),
			hametuha_taxonomy_for_title( 'category', get_queried_object() ),
			get_bloginfo( 'name' ),
			mysql2date( 'Y', get_queried_object()->post_date )
		);
	} elseif ( is_singular( 'series' ) ) {
		// 連載
		return sprintf(
			'%s『%s』（%s, %d年-, %s）',
			hametuha_author_name( get_queried_object() ),
			get_the_title( get_queried_object() ),
			get_bloginfo( 'name' ),
			mysql2date( 'Y', get_queried_object()->post_date ),
			\Hametuha\Model\Series::get_instance()->is_finished( get_queried_object_id() ) ? '完結' : '連載中'
		);
	} elseif ( is_singular( 'news' ) ) {
		// ニュース
		return implode( $sep, [
			get_the_title( get_queried_object() ),
			hametuha_taxonomy_for_title( 'genre', get_queried_object() ) . 'ニュース',
			get_bloginfo( 'name' ),
		] );
	}
	if ( is_category() ) {
		$title = get_queried_object()->name;
	} elseif ( is_tag() ) {
		$title = sprintf( 'タグ「%s」を含む作品', get_queried_object()->name );
	} elseif ( is_ranking() ) {
		$title = ranking_title() . " {$sep} ";
	} elseif ( is_singular( 'info' ) ) {
		$title .= "おしらせ {$sep} ";
	} elseif ( is_singular( 'faq' ) ) {
		$title = get_the_title() . $sep . 'よくある質問';
	} elseif ( is_tax( 'faq_cat' ) ) {
		$title = sprintf(
			'%1$sに関するよくある質問',
			get_queried_object()->name
		);
	} elseif ( is_post_type_archive( 'announcement' ) ) {
		$title = '告知';
	} elseif ( is_singular( 'announcement' ) ) {
		$title = get_the_title( get_queried_object() ) . $sep . '告知';
	} elseif ( is_singular( 'anpi' ) ) {
		$title .= '安否情報';
	} elseif ( is_singular( 'ideas' ) || is_post_type_archive( 'ideas' ) ) {
		$title .= 'アイデア';
	} elseif ( is_singular( 'thread' ) ) {
		$title = sprintf(
			'%s%s掲示板',
			get_the_title( get_queried_object() ),
			$sep
		);
	} elseif ( is_post_type_archive( 'thread' ) ) {
		$title = '掲示板';
	} elseif ( is_tax( 'topic' ) ) {
		$title = sprintf( 'トピック「%s」を含む掲示板', get_queried_object()->name );
	} elseif ( is_author() ) {
		$title = get_queried_object()->display_name . 'の作品一覧';
	} elseif ( is_search() ) {
		$title = sprintf( '「%s」の検索結果', get_search_query() );
	} elseif ( is_page() ) {
		$title = single_post_title( '', false );
	}
	// Merge title.
	$titles = [ $title ];

	$titles[] = get_bloginfo( 'name' );
	return implode( $sep, $titles );
}, 10, 3 );

/**
 * Return taxonomy name combined with separator.
 *
 * @param string           $taxonomy Taxonomy name.
 * @param null|int|WP_Post $post     Post object.
 * @param string           $sep      Separator.
 * @return string
 */
function hametuha_taxonomy_for_title( $taxonomy, $post = null, $sep = '・' ) {
	$terms = get_the_terms( get_post( $post ), $taxonomy );
	return ( ! $terms || is_wp_error( $terms ) ) ? '' : implode( $sep, array_map( function( $term ) {
		return $term->name;
	}, $terms ) );
}

/**
 * Faviconの表示
 */
function _hametuha_favicon() {
	?>
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/favicon.ico"/>
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
	$image    = get_template_directory_uri() . '/assets/img/facebook-logo-2016.png';
	$title    = wp_title( '|', false, 'right' );
	$url      = false;
	$type     = 'article';
	$creator  = '@hametuha';
	$desc     = '';
	$card     = 'summary';
	$author   = '';
	$twitters = [];

	// はめにゅーのときだけ画像を設定
	if ( is_hamenew() ) {
		$image = get_template_directory_uri() . '/assets/img/ogp/hamenew-ogp.png?201608';
	}
	//個別設定
	if ( is_front_page() ) {
		$url   = trailingslashit( get_bloginfo( 'url' ) );
		$type  = 'website';
		$page  = get_post( get_option( 'page_on_front' ) );
		$desc  = $page->post_excerpt;
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $page->ID ), 'full' )[0];
	} elseif ( 'kdp' == get_query_var( 'meta_filter' ) ) {
		$url   = home_url( '/kdp/' );
		$desc  = '破滅派初の電子書籍はAmazonのKindleで入手できます。';
		$image = get_stylesheet_directory_uri() . '/assets/img/jumbotron/kdp.jpg';
	} elseif ( is_author() ) {
		global $wp_query;
		$user   = get_userdata( $wp_query->query_vars['author'] );
		$type   = 'profile';
		$url    = get_author_posts_url( $user->ID, $user->user_nice_name );
		$image  = preg_replace( "/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar( $user->ID, 300 ) );
		$desc   = str_replace( "\n", '', get_user_meta( $user->ID, 'description', true ) );
		$author = '<meta property="profile:username" content="' . $user->user_login . '" />';
	} elseif ( is_singular() ) {
		$post   = get_queried_object();
		$url    = get_permalink( $post );
		$desc   = get_the_excerpt( $post );
		$author = '<meta property="article:author" content="' . get_author_posts_url( $post->post_author ) . '" />';
		if ( $screen_name = get_user_meta( $post->post_author, 'twitter', true ) ) {
			$creator = '@' . $screen_name;
		}
		if ( is_singular( 'thread' ) ) {
			// Show avatar on thread.
			$image = preg_replace( "/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar( $post->post_author, 300 ) );
		} elseif ( has_post_thumbnail() ) {
			// Show thumbnail if set.
			if ( $src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ) ) {
				$image = $src[0];
				if ( $src[1] >= 696 ) {
					$card = 'summary_large_image';
				}
			}
			// Show product card
			if ( is_singular( 'series' ) ) {
				$series = \Hametuha\Model\Series::get_instance();
				if ( 2 == $series->get_status( $post->ID ) ) {
					// If this is e-book and sold...
					// $card               = 'product';
					$twitters['label1'] = '価格';
					$twitters['data1']  = '&yen;' . number_format( get_series_price( $post ) );
					if ( $subtitle = $series->get_subtitle( $post->ID ) ) {
						$twitters['label2'] = 'ジャンル';
						$twitters['data2']  = $subtitle;
					}
				}
			}
		} else {
			// 先頭にあるものを表示する
			$attachments = get_posts( [
				'post_parent'    => $post->ID,
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			] );
			foreach ( $attachments as $attachment ) {
				if ( $src = wp_get_attachment_image_src( $attachment->ID, 'full' ) ) {
					if ( ! is_hamenew() || $src[1] >= 696 ) {
						$image = $src[0];
					}
				}
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$url  = get_term_link( $term );
		$desc = $term->description;
	} elseif ( is_ranking() ) {
		$url   = home_url( $_SERVER['REQUEST_URI'] );
		$image = get_stylesheet_directory_uri() . '/assets/img/jumbotron/ranking.jpg';
	} elseif ( is_post_type_archive() ) {
		$post_obj = get_post_type_object( get_query_var( 'post_type' ) ?: 'post' );
		$url      = get_post_type_archive_link( get_post_type() );
		$desc     = $post_obj->description ?: '';
		$path     = '/assets/img/jumbotron/' . get_post_type() . '.jpg';
		if ( file_exists( get_stylesheet_directory() . $path ) ) {
			$image = get_stylesheet_directory_uri() . $path;
		}
	} elseif ( ( $class_name = get_query_var( 'api_class' ) ) ) {
		$class_name = str_replace( '\\\\', '\\', $class_name );
		if ( class_exists( $class_name ) && method_exists( $class_name::get_instance(), 'ogp' ) ) {
			extract( $class_name::get_instance()->ogp( compact( 'image', 'title', 'url', 'type', 'desc', 'card', 'author' ) ) );
		}
		$url = home_url( trailingslashit( explode( '?', $_SERVER['REQUEST_URI'] )[0] ) );
	} elseif ( is_home() ) {
		$url  = get_permalink( get_option( 'page_for_posts' ) );
		$desc = '破滅派の新着投稿一覧です。';
	} else {
		$url = home_url( trailingslashit( explode( '?', $_SERVER['REQUEST_URI'] )[0] ) );
	}
	$desc  = esc_attr( str_replace( "\n", '', $desc ) );
	$image = str_replace( '://s.', '://', $image );
	if ( ! $url || is_wp_error( $url ) ) {
		return;
	}
	$twitters = array_merge( [
		'card'    => $card,
		'site'    => '@hametuha',
		'domain'  => 'hametuha.com',
		'creator' => $creator,
		'title'   => $title,
		'desc'    => $desc,
		'image'   => $image,
	], $twitters );
	echo <<<HTML
<title>{$title}</title>
<meta name="description" content="{$desc}" />
<!-- OGP -->
<meta property="og:title" content="{$title}"/>
<meta property="og:url" content="{$url}" />
<meta property="og:image" content="{$image}" />
<meta property="og:description" content="{$desc}" />
<meta property="og:type" content="{$type}" />
{$author}
<meta property="article:publisher" content="https://www.facebook.com/hametuha.inc" />
<meta property="og:site_name" content="破滅派｜オンライン文芸誌" />
<meta property="og:locale" content="ja_jp" />
<meta property="fb:admins" content="1034317368" />
<meta property="fb:app_id" content="196054397143922" />
<meta property="fb:pages" content="196103120449777,112969535414323" />
<!-- twitter cards -->
HTML;
	foreach ( $twitters as $key => $content ) {
		printf( '<meta name="twitter:%s" content="%s" />', $key, $content );
	}
}, 1 );

/**
 * 検索エンジン対策
 *
 * 特定の投稿で「検索エンジンに表示しない」がオンになっていたら、
 * noindexを出力する。
 */
add_action( 'wp_head', function() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	if ( 'noindex' === get_post_meta( get_queried_object_id(), '_noindex', true ) ) {
		echo '<meta name="robots" content="noindex,noarchive" />';
	}
} );
