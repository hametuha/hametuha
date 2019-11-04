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

