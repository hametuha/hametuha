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
	if ( $wp_query->is_main_query() && is_hamenew() && ! is_singular( 'news' ) ) {
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
	$term_ids = [ ];
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
	if ( ! $asin || ! class_exists( 'WP_Hamazon_Controller' ) || ( ! WP_Hamazon_Controller::get_instance()->amazon ) ) {
		return [ ];
	}

	return array_filter( array_map( function ( $code ) {
		$result = WP_Hamazon_Controller::get_instance()->amazon->get_itme_by_asin( $code );
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
			$src = false;
		}

		return [ $title, $url, $src, $author, $publisher, $rank ];
	}, explode( "\r\n", $asin ) ) );
}

/**
 * 人気のキーワードを返す
 *
 * @return array
 */
function hamenew_popular_nouns() {
	$terms = get_terms( 'nouns' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return [];
	}
	// Filter terms
	$terms = array_filter( $terms, function ( $term ) {
		return 1 < $term->count;
	} );
	usort( $terms, function ( $a, $b ) {
		if ( $a->count === $b->count ) {
			return 0;
		} else {
			return $a->count > $b->count ? - 1 : 1;
		}
	} );

	return $terms;
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
add_action( 'save_post', function( $post_id, $post ) {
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
			$urls[] = trailingslashit( get_permalink( $post ) ).'page/'.($index + 1).'/';
		}
		$urls[] = trailingslashit( get_permalink( $post ) ) . 'amp/';
	}
	// タクソノミー
	foreach ( [ 'genre', 'nouns' ] as $taxonomy ) {
		if ( ( $terms = get_the_terms( $post, $taxonomy ) ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$url = get_term_link( $term );
				if ( false === array_search( $url, $urls ) ) {
					$url[] = $url;
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
 * ニュースが更新されたときの通知
 *
 * @param string $new_status
 * @param string $old_status
 * @param WP_Post $post
 */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	// ニュース以外は無視
	if ( 'news' !== $post->post_status || WP_DEBUG ) {
		return;
	}
	$author = get_userdata( $post->post_author );
	switch ( $new_status ) {
		case 'pending':
			if ( ! user_can( $post->post_author, 'edit_others_posts' ) ) {
				switch ( $old_status ) {
					case 'pending':
						// 何もしない
						break;
					case 'publish':
						// 公開されていたものが非公開になった
						// TODO: なんらかの方法で連絡する
						break;
					default:
						// 承認待ちになった
						$title = get_the_title( $post );
						hametuha_slack( '@here ニュースが承認待ちです。', [
							'fallback' => $title,
							'title' => $title,
							'title_link' => get_edit_post_link( $post->ID, 'mail' ),
							'author_name' => $author->display_name,
							'author_link' => home_url( "/doujin/detail/{$author->user_nicename}/" ),
							'color' => '#00928D',
						], '#news' );
						break;
				}
			}
			break;
		case 'publish':
			// 投稿が公開された
			switch ( $old_status ) {
				case 'publish':
				case 'private':
					// なにもしない
					break;
				default:
					// 公開された
					$string = sprintf( '#はめにゅー 更新 「%s」 %s', get_the_title( $post ), get_permalink( $post ) );
					if ( function_exists( 'update_twitter_status' ) ) {
						update_twitter_status( $string );
					}
					// Slackに通知
					hametuha_slack( $string, [], '#news' );
					break;
			}
			break;
	}
}, 10, 3 );
