<?php
/**
 * 投稿のリストを取得する関数
 */

/**
 * 最新の投稿をユーザーの重複なく取得する
 *
 * @param int $limit
 * @param string $post_type Default post.
 *
 * @return array
 */
function hametuha_recent_posts( $limit = 5, $post_type = 'post' ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$sql    = <<<SQL
      SELECT * FROM (
        SELECT * FROM {$wpdb->posts}
        WHERE post_type = %s
          AND post_status = 'publish'
        ORDER BY post_date DESC
    ) AS p
    GROUP BY post_author
    ORDER BY post_date DESC
    LIMIT %d

SQL;
	$result = [];
	foreach ( $wpdb->get_results( $wpdb->prepare( $sql, $post_type, $limit ) ) as $row ) {
		$result[] = new WP_Post( $row );
	}

	return $result;
}

/**
 * ジャンル別の統計情報を返す
 *
 * @param int $limit
 *
 * @return array
 */
function hametuha_genre_static( $limit = 0 ) {
	$categories = get_terms( 'category' );
	$total      = 0;
	foreach ( $categories as &$cat ) {
		$total += $cat->count;
		$cat->url = get_category_link( $cat );
	}
	usort( $categories, function ( $a, $b ) {
		if ( $a->count == $b->count ) {
			return 0;
		} else {
			return $a->count < $b->count ? 1 : - 1;
		}
	} );

	return [
		'total'      => $total,
		'categories' => $categories,
	];
}


/**
 * 最新のシリーズを取得する
 *
 * @param int $limit
 *
 * @return array
 */
function hametuha_recent_series( $limit = 5 ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$sql    = <<<SQL
      SELECT DISTINCT s.*, COUNT(p.ID) AS children, MAX(p.post_date) AS latest
      FROM {$wpdb->posts} AS s
      LEFT JOIN {$wpdb->posts} AS p
      ON s.ID = p.post_parent
      WHERE s.post_type = 'series'
        AND s.post_status = 'publish'
        AND p.post_type = 'post'
        AND p.post_status = 'publish'
      GROUP BY s.ID
      ORDER BY latest DESC
      LIMIT %d
SQL;
	$result = [];
	foreach ( $wpdb->get_results( $wpdb->prepare( $sql, $limit ) ) as $row ) {
		$result[] = new WP_Post( $row );
	}

	return $result;
}
