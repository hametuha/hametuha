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
    // 著者名を追加する
    if ( is_singular( [ 'post', 'series' ] ) ) {
        $title .= sprintf( '%s %s ', hametuha_author_name( get_queried_object() ), $sep );
    }
    // その他
	if ( is_singular( 'post' ) ) {
	    // 投稿の場合はカテゴリーを追加
		$cats = get_the_category( get_queried_object_id() );
		if ( ! empty( $cats ) ) {
		    $title .= sprintf( '%s %s ', current( $cats )->name, $sep );
		}
	} elseif ( is_singular( 'series' ) ) {
	    // 作品集の場合は電子書籍だったら電子書籍
        $label = \Hametuha\Model\Series::get_instance()->get_status( get_queried_object_id() ) ? '電子書籍' : '作品集';
		$title .= sprintf( '%s %s ', $label, $sep );
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
	} elseif ( is_singular( 'ideas' ) ) {
		$title .= "アイデア {$sep} ";
	} elseif ( is_post_type_archive( 'ideas' ) ) {
		$title = "アイデア {$sep}";
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
 * タイトルを変更
 *
 * @todo get_document_titleが標準になったら消す
 * @param array
 */
add_filter( 'document_title_parts', function( $title ){
	if ( is_singular( 'news' ) ) {
		$title = [ hamenew_copy( get_the_title() ) ];
	}
	return $title;
} );

/**
 * タイトルタグのセパレータを変更
 */
add_filter( 'document_title_separator', function(){
	return '|';
} );

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
add_action( 'amp_post_template_head', '_hametuha_favicon' );

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
	if ( is_hamenew() ) {
		if ( is_tax( 'nouns' ) || is_tax( 'genre' ) ) {
			$object = get_queried_object();
			$taxonomy = get_taxonomy( $object->taxonomy );
			$label = is_tax( 'nouns' ) ? 'キーワード' : esc_html( $taxonomy->label );
			$title = hamenew_copy( sprintf( '%2$s「%1$s」のニュース', esc_html( $object->name ), $label ) );
		} elseif ( is_singular( 'news' ) ) {
			$terms = get_the_terms( get_queried_object(), 'genre' );
			$seg   = [];
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$seg[] = esc_html( $term->name ) . 'ニュース';
					break;
				}
			}
			array_unshift( $seg, esc_html( get_the_title() ) );
			$title = hamenew_copy( implode( ' | ', $seg ) );
		} elseif ( is_page_template( 'page-hamenew.php' ) ) {
			$title = hamenew_copy( get_the_title() );
		} else {
			$title = hamenew_copy( );
		}
	} else {
		$title    = wp_title( '|', false, 'right' ) . get_bloginfo( 'name' );
	}
	$url      = false;
	$type     = 'article';
	$creator  = '@hametuha';
	$desc     = '';
	$card     = 'summary_large_image';
	$author   = '';
	$twitters = [];

	global $wp_query;

	// はめにゅーのときだけ画像を設定
	if ( is_hamenew() ) {
		$image = get_template_directory_uri().'/assets/img/ogp/hamenew-ogp.png?201608';
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
		$desc  = '破滅派初の電子書籍はAmazonのKindleで入手できます。プライム会員は月1冊まで無料！';
		$image = get_stylesheet_directory_uri() . '/assets/img/jumbotron/kdp.jpg';
	} elseif ( is_author() ) {
		global $wp_query;
		$user   = get_userdata( $wp_query->query_vars['author'] );
		$title  = $user->display_name;
		$type   = 'profile';
		$url    = get_author_posts_url( $user->ID, $user->user_nice_name );
		$image  = preg_replace( "/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar( $user->ID, 300 ) );
		$desc   = str_replace( "\n", '', get_user_meta( $user->ID, 'description', true ) );
		$author = '<meta property="profile:username" content="' . $user->user_login . '" />';
		$card   = 'summary';
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$url = get_permalink( $post );
		$desc = get_the_excerpt( $post );
		$author = '<meta property="article:author" content="' . get_author_posts_url( $post->post_author ) . '" />';
		if ( $screen_name = get_user_meta( $post->post_author, 'twitter', true ) ) {
			$creator = '@' . $screen_name;
		}
		if ( is_singular( 'thread' ) ) {
			// Show avatar on thread.
			$image = preg_replace( "/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar( $post->post_author, 300 ) );
			$card  = 'summary';
		} elseif ( has_post_thumbnail() ) {
			// Show thumbnail if set.
			if ( $src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ) ) {
				$image = $src[0];
				if ( $src[1] >= 696 ) {
					$card  = 'summary_large_image';
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
						$card  = 'summary_large_image';
					}
				}
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$url = get_term_link( $term );
		$desc = $term->description;
	} elseif ( is_ranking() ) {
		$url   = home_url( $_SERVER['REQUEST_URI'] );
		$image = get_stylesheet_directory_uri() . '/assets/img/jumbotron/ranking.jpg';
		$card  = 'summary_large_image';
	} elseif ( is_post_type_archive() ) {
		$post_obj = get_post_type_object( get_query_var( 'post_type' ) ?: 'post' );
		$url      = get_post_type_archive_link( get_post_type() );
		$desc     = $post_obj->description ?: '';
		$path     = '/assets/img/jumbotron/' . get_post_type() . '.jpg';
		if ( file_exists( get_stylesheet_directory() . $path ) ) {
			$image = get_stylesheet_directory_uri() . $path;
			$card  = 'summary_large_image';
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
	if ( ! $url ) {
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
 * リッチスニペット
 */
add_action( 'wp_head', function () {
	$url  = home_url( '/' );
	$name = get_bloginfo( 'name' );
	$css_dir = get_template_directory_uri();
	if ( is_front_page() ) {
		echo <<<HTML
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "WebSite",
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
	if ( is_singular( 'news' ) ) {
		$excerpt = preg_replace( '#[\r|\n]#', '', strip_tags( get_the_excerpt() ) );
		$image = [
			get_template_directory_uri().'/assets/img/ogp/hamenew-ogp.png',
		    '1200',
		    '696',
		];
		if ( has_post_thumbnail() ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		}
		?>
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "NewsArticle",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "<?php the_permalink() ?>"
  },
  "headline": "<?= esc_js( get_the_title() ) ?>",
  "image": {
    "@type": "ImageObject",
    "url": "<?= $image[0] ?>",
    "height": <?= $image[1] ?>,
    "width": <?= $image[2] ?>
  },
  "datePublished": "<?php the_date( DateTime::ATOM ) ?>",
  "dateModified": "<?php the_modified_date( DateTime::ATOM ) ?>",
  "author": {
    "@type": "Person",
    "name": "<?= esc_js( get_the_author() ) ?>"
  },
   "publisher": {
    "@type": "Organization",
    "name": "<?php bloginfo( 'name' ) ?>",
    "logo": {
      "@type": "ImageObject",
      "url": "<?= get_template_directory_uri() ?>/assets/img/ogp/hamenew-company.png",
      "width": 600,
      "height": 60
    }
  },
  "description": "<?= esc_js( $excerpt ) ?>"
}
</script>
		<?php
	}
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
		"https://plus.google.com/+HametuhaCom"
	]
}
</script>
HTML;
} );

/**
 * 検索エンジン対策
 */
add_action( 'wp_head', function() {
    if ( ! is_singular( 'post' ) ) {
        return;
    }
    if ( 'noindex' === get_post_meta( get_queried_object_id(), '_noindex', true ) ) {
        echo '<meta name="robots" content="noindex,noarchive" />';
    }
} );

/**
 * サイトマップから削除
 */
add_filter( 'bwp_gxs_excluded_posts', function( $excludes, $requested ) {
    global $wpdb;
    $query = <<<SQL
        SELECT p.ID FROM {$wpdb->posts} AS p
        INNER JOIN {$wpdb->postmeta} AS pm
        ON p.ID = pm.post_id AND pm.meta_key = '_noindex'
        WHERE p.post_status = 'publish'
          AND pm.meta_value = 'noindex'
SQL;
    return array_map( 'intval', array_filter( array_merge( $excludes, $wpdb->get_col( $query ) ) ) );
}, 10, 2 );
