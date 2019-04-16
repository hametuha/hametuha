<?php
/**
 * ニュース関連の処理
 */

/**
 * はめにゅーのタイトル
 *
 * @param string $prefix
 * @param string $sep
 *
 * @return string
 */
function hamenew_copy( $prefix = '', $sep = '|' ) {
	$titles = [ 'はめにゅー' ];
	if ( $prefix ) {
		array_unshift( $titles, $prefix );
	} else {
		$titles[] = '破滅派がお送りする文学関連ニュース';
	}

	return implode( " {$sep} ", $titles );
}

/**
 * ニュース以外はamp無効
 */
add_filter( 'amp_skip_post', function ( $skip, $post_id, $post ) {
	return 'news' !== $post->post_type;
}, 10, 3 );

/**
 * 広告を挿入する
 */
add_action( 'wp_head', function () {
	// Googleの広告
	if ( is_hamenew() ) {
		echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "ca-pub-0087037684083564",
    enable_page_level_ads: true
  });
</script>
HTML;
	}
}, 1 );

/**
 * ニュース画面だったら
 *
 * @return bool
 */
function is_hamenew() {
	if ( is_front_page() ) {
		return false;
	}

	return is_singular( 'news' ) || is_tax( 'nouns' ) || is_tax( 'genre' ) || is_post_type_archive( 'news' ) || is_page_template( 'page-hamenew.php' );
}

/**
 * 広告記事か否か
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function hamenew_is_pr( $post = null ) {
	$post = get_post( $post );

	return get_post_meta( $post->ID, '_advertiser', true ) || get_post_meta( $post->ID, '_is_owned_ad', true );
}

/**
 * 広告主の名前を返す
 *
 * @param null|int|WP_Post $post
 *
 * @return mixed|string
 */
function hamenew_pr_label( $post = null ) {
	$post   = get_post( $post );
	$string = '';
	if ( get_post_meta( $post->ID, '_is_owned_ad', true ) ) {
		$string = '破滅派';
	}
	if ( $advertiser = get_post_meta( $post->ID, '_advertiser', true ) ) {
		$string = $advertiser;
	}

	return $string;
}

/**
 * ニュースだったらテンプレートを切り替える
 */
add_filter( 'template_include', function ( $path ) {
	if ( is_singular( 'news' ) ) {
		$path = get_template_directory() . '/templates/news/single.php';
	} elseif ( is_tax( 'nouns' ) || is_tax( 'genre' ) || ( is_post_type_archive( 'news' ) && 1 < (int) get_query_var( 'paged' ) ) ) {
		$path = get_template_directory() . '/templates/news/archive.php';
	} elseif ( is_post_type_archive( 'news' ) ) {
		$path = get_template_directory() . '/templates/news/front.php';
	}

	return $path;
} );

/**
 * ニュースページの場合は20件にする
 */
add_action( 'pre_get_posts', function ( &$wp_query ) {
	if ( $wp_query->is_main_query() && ( $wp_query->is_tax( [
				'nouns',
				'genre'
			] ) || $wp_query->is_post_type_archive( 'news' ) ) && ! $wp_query->is_singular( 'news' )
	) {
		$wp_query->set( 'posts_per_page', 20 );
	}
} );

/**
 * 関連記事を取得する
 *
 * @param int $limit
 * @param null|int|WP_Post $post
 *
 * @return array
 */
function hamenew_related( $limit = 5, $post = null ) {
	global $wpdb;
	$post     = get_post( $post );
	$term_ids = [];
	foreach ( [ 'nouns', 'genre' ] as $tax ) {
		$terms = get_the_terms( $post, $tax );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_ids[] = $term->term_taxonomy_id;
			}
		}
	}
	if ( ! $term_ids ) {
		return [];
	}
	$term_ids = implode( ', ', $term_ids );
	$query    = <<<SQL
		SELECT * FROM {$wpdb->posts} AS p
		LEFT JOIN (
			SELECT object_id, COUNT( term_taxonomy_id ) AS score
			FROM {$wpdb->term_relationships}
			WHERE term_taxonomy_id IN ( {$term_ids} )
			GROUP BY object_id
		) as t
		ON p.ID = t.object_id
		WHERE p.post_type = 'news'
		  AND p.post_status = 'publish'
		  AND p.ID != %d
		ORDER BY t.score DESC, p.post_date DESC
		LIMIT %d
SQL;

	return array_map( function ( $post ) {
		return new WP_Post( $post );
	}, $wpdb->get_results( $wpdb->prepare( $query, $post->ID, $limit ) ) );
}

/**
 * 日付をイベント書式にして返す
 *
 * @param string $from
 * @param string $to
 * @param string $date_format
 * @param string $time_format
 *
 * @return string
 */
function hamenew_event_date( $from, $to = '', $date_format = 'Y年n月j日（D）', $time_format = 'H:i' ) {
	$format = $date_format . ' ' . $time_format;
	if ( ! $to ) {
		return mysql2date( $format, $from );
	}
	if ( mysql2date( 'Y-m-d', $from ) == mysql2date( 'Y-m-d', $to ) ) {
		return mysql2date( $format, $from ) . '〜' . mysql2date( $time_format, $to );
	} else {
		return mysql2date( $date_format, $from ) . '〜' . mysql2date( $date_format, $to );
	}
}

/**
 * ニュースの関連リンクを返す
 *
 * @param null|int|WP_Post $post
 *
 * @return array
 */
function hamenew_links( $post = null ) {
	$post  = get_post( $post );
	$links = get_post_meta( $post->ID, '_news_related_links', true );
	if ( ! $links ) {
		return [];
	}

	return array_filter( array_map( function ( $line ) {
		$line = explode( '|', $line );
		if ( 2 > count( $line ) ) {
			return false;
		}
		$url   = array_shift( $line );
		$title = implode( '|', $line );

		return [ $title, $url ];
	}, explode( "\r\n", $links ) ), function ( $var ) {
		return $var && is_array( $var );
	} );
}

/**
 * 関連書籍を表示する
 *
 * @param null|int|WP_Post $post
 *
 * @return array
 */
function hamenew_books( $post = null ) {
	$post = get_post( $post );
	$asin = get_post_meta( $post->ID, '_news_related_books', true );
	if ( ! $asin || ! class_exists( 'Hametuha\WpHamazon\Constants\AmazonConstants' ) ) {
		return [];
	}

	return array_filter( array_map( function ( $code ) {
		$result = \Hametuha\WpHamazon\Constants\AmazonConstants::get_item_by_asin( $code );
		if ( is_wp_error( $result ) || 'False' === (string) $result->Items->Request->IsValid ) {
			return false;
		}
		$item  = $result->Items->Item[0];
		$url   = (string) $item->DetailPageURL;
		$title = (string) $item->ItemAttributes->Title;
		if ( ! $url || ! $title ) {
			return false;
		}
		$rank      = (int) $item->SalesRank;
		$publisher = (string) $item->ItemAttributes->Publisher;
		$author    = (string) $item->ItemAttributes->Author;
		if ( isset( $item->LargeImage ) ) {
			$src = (string) $item->LargeImage->URL;
		} else {
			$src = hamazon_no_image();
		}

		return [ $title, $url, $src, $author, $publisher, $rank ];
	}, explode( "\r\n", $asin ) ) );
}

/**
 * 人気のキーワードを返す
 *
 * @param int $term_id Default 0
 * @param int $days    Default 30
 * @param int $limit   Default 20
 * @return array
 */
function hamenew_popular_nouns( $term_id = 0, $days = 30, $limit = 20 ) {
	global $wpdb;
	$wheres = [
		"( tt.taxonomy = 'nouns' )"
	];
	// Filter term id
	if ( $term_id ) {
		$term = get_term( $term_id, 'nouns' );
		if ( ! $term || is_wp_error( $term ) ) {
			return [];
		}
		$ids      = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d", $term_id ) );
		if ( ! $ids ) {
			return [];
		}
		$ids = implode( ', ', $ids );
		$wheres[] = $wpdb->prepare( '(tt.term_taxonomy_id != %d)', $term->term_taxonomy_id );
		$wheres[] = <<<SQL
			( tt.term_taxonomy_id IN (
				SELECT term_taxonomy_id FROM {$wpdb->term_relationships}
				WHERE object_id IN ({$ids})
			    GROUP BY term_taxonomy_id
			) )
SQL;
	}
	// Filter days
	if ( $days ) {
		$days = (int) $days;
		$wheres[] = <<<SQL
			( tt.term_taxonomy_id IN (
			  SELECT tr.term_taxonomy_id FROM {$wpdb->term_relationships} AS tr
			  LEFT JOIN {$wpdb->posts} AS p
			  ON p.ID = tr.object_id
			  WHERE p.post_type = 'news'
			    AND p.post_status = 'publish'
			    AND DATE_ADD(p.post_date, INTERVAL {$days} DAY) > NOW()
			) )
SQL;
	}
	$wheres = $wheres ? " WHERE " . implode( ' AND ', $wheres ) : '';
	$query = <<<SQL
		SELECT t.*, tt.* FROM {$wpdb->terms} AS t
		INNER JOIN {$wpdb->term_taxonomy} AS tt
		ON t.term_id = tt.term_id
		{$wheres}
		ORDER BY tt.count DESC
SQL;
	if ( $limit ) {
		$query .= sprintf( ' LIMIT %d', $limit );
	}
	return $wpdb->get_results( $query );
}

/**
 * ニュースアーカイブのタイトルを修正する
 *
 * @param string $name
 *
 * @return string
 */
add_filter( 'single_term_title', function ( $name ) {
	if ( is_tax( 'nouns' ) ) {
		$name = sprintf( 'キーワード「%s」を含む記事', $name );
	} elseif ( is_tax( 'genre' ) ) {
		$name = sprintf( 'ジャンル「%s」の記事', $name );
	}

	return $name;
} );

/**
 * ニュースが更新されたとき
 *
 * @param int $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function ( $post_id, $post ) {
	if ( 'news' !== $post->post_type || 'publish' !== $post->post_status ) {
		return;
	}
	// クラウドフレアのキャッシュをすべて削除する
	$urls = [
		home_url( '/' ),
		get_post_type_archive_link( 'news' ),
	];
	// パーマリンク
	foreach ( explode( '<!--nextpage-->', $post->post_content ) as $index => $content ) {
		if ( $index ) {
			$urls[] = get_permalink( $post );
		} else {
			$urls[] = trailingslashit( get_permalink( $post ) ) . 'page/' . ( $index + 1 ) . '/';
		}
	}
	// AMP
    $urls[] = trailingslashit( get_permalink( $post ) ) . 'amp/';
	// タクソノミー
	foreach ( [ 'genre', 'nouns' ] as $taxonomy ) {
		if ( ( $terms = get_the_terms( $post, $taxonomy ) ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$url = get_term_link( $term );
				if ( false === array_search( $url, $urls ) ) {
					$urls[] = $url;
				}
				if ( $term->parent ) {
					$parent = get_term( $term->parent, $term->taxonomy );
					if ( $parent && ! is_wp_error( $parent ) ) {
						$p_url = get_term_link( $parent );
						if ( false === array_search( $p_url, $urls ) ) {
							$urls[] = $p_url;
						}
					}
				}
			}
		}
	}
	// キャッシュを消す
	cf_purge_cache( $urls );
}, 10, 2 );


/**
 * ヘルプメニューを追加
 */
add_action( 'admin_head', function () {
	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! ( $screen = get_current_screen() ) || 'news' != $screen->post_type ) {
		return;
	}
	foreach (
		[
			'publish'   => [
				'公開フロー',
				'<p>ニュースは「レビュー待ち」として送信されたのち、破滅派編集部によるチェックを経て公開されます。なるべく早く行いますが、24時間365日で対応することはできませんので、その点ご了承ください。</p>',
			],
			'published' => [
				'公開済みニュース',
				'<p>一度公開されたニュースは破滅派編集部以外編集できません。修正要望がある場合はSLACKにてお問い合わせください。</p>',
			],
			'banned'    => [
				'ボツニュース',
				'<p>ニュースのステータスが「非公開」となっている場合、そのニュースはボツになっています。ボツになったニュースはもう編集できません。理由についてはSLACKにてお伝えしますので、お問い合わせください。</p>',
			],
			'contact'   => [
                '連絡方法',
                '<p>ニュースの連絡におけるすべてのやりとりは基本的にSLACKで行います。参加方法はよくある質問をご覧ください。</p>'
            ],
		] as $id => list( $title, $content )
	) {
		$screen->add_help_tab( [
			'id'      => 'news-' . $id,
			'title'   => $title,
			'content' => $content,
		] );
	}
	if ( isset( $_GET['post'] ) ) {
        $screen->add_help_tab( [
            'id' => 'news-preview',
            'title' => 'プレビュー',
            'content' => sprintf(
				'<p>はめにゅーは様々なフォーマットで公開されます。<a href="%s" target="_blank">Instant Article</a>や<a href="%s" target="_blank">AMP</a>でも見栄えもチェックしてください。</p>',
				home_url( sprintf( 'instant-article/preview/%d/?preview_instant_article=true', $_GET['post'] ) ),
				amp_get_permalink( $_GET['post'] )
			),
        ] );
    }

	// サイドバーを追加
	$term = get_term_by( 'slug', 'news', 'faq_cat' );
	if ( $term ) {
		$url = get_term_link( $term );
	} else {
		$url = get_post_type_archive_link( 'faq' );
	}
	$sidebar = <<<HTML
<ul>
	<li><a href="{$url}" target="_blank">よくある質問</a></li>
	<li><a href="https://hametuha.slack.com" target="_blank">破滅派SLACK</a></li>
</ul>
HTML;
	$screen->set_help_sidebar( $sidebar );
} );

/**
 * XMLサイトマップを追加
 *
 * @todo いまのところ、Googleに無視されているので、あとでやる
 */
add_filter( 'bwp_gxs_external_sitemaps', function ( $data ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$query = <<<SQL
		SELECT COUNT(ID) FROM {$wpdb->posts}
		WHERE post_type = 'news'
          AND post_status = 'publish'
SQL;
	$total = (int) $wpdb->get_var( $query );
	$per_page = get_option( 'posts_per_rss', 20 );
	for ( $i = 0, $l = ceil( $total / $per_page ); $i < $l; $i++ ) {
		$url = home_url( '/amp_sitemap/' ).( $i ? sprintf( '?paged=%d', $i + 1 ) : '' );
		$data[] = [
			'location' => $url,
		];
	}
	$post = get_posts( [
		'post_type' => 'news',
		'post_status' => 'publish',
	    'posts_per_page' => 1,
	    'orderby' => [ 'date' => 'DESC' ],
	] );
	return $data;
} );

/**
 * サイトマップ用フィードを作成
 */
add_action( 'pre_get_posts', function ( WP_Query &$wp_query ) {
	if ( ! $wp_query->is_main_query() || ! $wp_query->is_feed ) {
		return;
	}
} );

/**
 * amp用サイトマップ
 */
add_action( 'do_feed_amp_sitemap', function () {
	header( 'Content-Type: text/xml; charset=UTF-8' );
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	?>
	<urlset
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
		xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		<?php while ( have_posts() ) : the_post(); ?>
			<url>
				<loc><?= trailingslashit( get_permalink() ) ?>amp/</loc>
				<lastmod><?php the_modified_time( DateTime::W3C ); ?></lastmod>
				<changefreq>weekly</changefreq>
				<priority>0.5</priority>
			</url>
		<?php endwhile; ?>
	</urlset>
	<?php
} );

/**
 * RSSのタイトルを変更
 *
 * @param string $title
 * @return string
 */
add_filter( 'get_wp_title_rss', function( $title ) {
	if ( is_post_type_archive( 'news' ) ) {
		$title = 'はめにゅー | 文芸関連ニュース';
	}
	return $title;
} );

/**
 * AMPを変更
 */
add_filter( 'bloginfo_rss', function($value, $show){
	if ( 'description' == $show && is_post_type_archive( 'news' ) ) {
		$value = get_post_type_object( 'news' )->description;
	}
	return $value;
}, 10, 2);
