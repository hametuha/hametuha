<?php
/**
 * 投稿のリストを取得する関数
 */

/**
 * 最新の投稿をユーザーの重複なく取得する
 *
 * @param int    $limit
 * @param string $post_type Default post.
 * @param int    $period
 *
 * @return array
 */
function hametuha_recent_posts( $limit = 5, $post_type = 'post', $period = 900 ) {
	$date = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 60 * 60 * 24 * $period );
	/** @var wpdb $wpdb */
	global $wpdb;
	$sql    = <<<SQL
      SELECT * FROM (
        SELECT * FROM {$wpdb->posts}
        WHERE post_type = %s
          AND post_status = 'publish'
          AND post_date >= %s
        ORDER BY post_date DESC
	    LIMIT %d
    ) AS p
    GROUP BY post_author
    ORDER BY post_date DESC
    LIMIT %d

SQL;
	$result = [];
	foreach ( $wpdb->get_results( $wpdb->prepare( $sql, $post_type, $date, $limit * 100, $limit ) ) as $row ) {
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
		$total   += $cat->count;
		$cat->url = get_category_link( $cat );
	}
	usort(
		$categories,
		function ( $a, $b ) {
			if ( $a->count == $b->count ) {
				return 0;
			} else {
				return $a->count < $b->count ? 1 : - 1;
			}
		}
	);

	return [
		'total'      => $total,
		'categories' => $categories,
	];
}


/**
 * 最新のシリーズを取得する
 *
 * @param int $limit
 * @param int $period
 *
 * @return WP_Post[]
 */
function hametuha_recent_series( $limit = 5, $period = 90 ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$date   = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 60 * 60 * 24 * $period );
	$sql    = <<<SQL
		select post_parent, COUNT(ID) AS children, MAX(post_date) AS latest
		FROM {$wpdb->posts}
		WHERE post_type = 'post'
		AND post_status = 'publish'
		AND post_date > %s
		AND post_parent != 0
		GROUP BY post_parent
		ORDER BY latest DESC
		LIMIT %d
SQL;
	$sql    = $wpdb->prepare( $sql, $date, $limit * 2 );
	$series = [];
	foreach ( $wpdb->get_results( $sql ) as $row ) {
		$series[ $row->post_parent ] = $row;
	}
	if ( ! $series ) {
		return [];
	}
	$posts = get_posts(
		[
			'post_type'      => 'series',
			'post_status'    => 'publish',
			'post__in'       => array_keys( $series ),
			'orderby'        => 'post__in',
			'posts_per_page' => $limit,
		]
	);
	return array_map(
		function( $post ) use ( $series ) {
			$post->children = $series[ $post->ID ]->children;
			return $post;
		},
		$posts
	);
}
