<?php
/**
 * ランキング関連の関数
 *
 * @feature-group ranking
 */

use Hametuha\QueryHighJack\RankingQuery;


/**
 * ランキングURLのショートコード
 * @param array $atts
 * @param string $content
 * @return string
 */
add_shortcode('ranking_url', function ( $atts = [], $content = '' ) {
	foreach ( [
		'url'  => home_url( '/ranking/weekly/' . get_latest_ranking_day( 'Ymd/' ) ),
		'date' => get_latest_ranking_day( get_option( 'date_format' ) ),
	] as $key => $repl ) {
		$content = str_replace( '%' . $key . '%', $repl, $content );
	}
	return $content;
});

/**
 * ランキングを出力する
 *
 * @param WP_Post $post
 */
function the_ranking( \WP_Post $post = null ) {
	echo number_format( get_the_ranking( $post ) );
}

/**
 * 最新の週間ランキング日を取得する
 *
 * @param string $format
 *
 * @return string
 */
function get_latest_ranking_day( $format = '' ) {
	$thursday = date_i18n( 'N' ) == 4 ? current_time( 'timestamp' ) : strtotime( 'Previous Thursday', current_time( 'timestamp' ) );
	$sunday   = strtotime( 'Previous Sunday', $thursday );
	return date_i18n( $format, $sunday );
}

/**
 * ランキングを取得する
 *
 * @param WP_Post $post
 * @return int
 */
function get_the_ranking( \WP_Post $post = null ) {
	$post = get_post( $post );
	return isset( $post->rank ) ? $post->rank : 1;
}

/**
 * 作者の一番人気のある作品を取得する
 *
 * @param null|int|WP_Post $post
 * @param int $limit
 * @return WP_Post[]
 */
function hametuha_get_author_popular_works( $post = null, $limit = 5 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	global $wpdb;
	$query = <<<SQL
		SELECT p.* FROM (
			SELECT * FROM {$wpdb->posts}
			WHERE post_type   = 'post'
			  AND post_status = 'publish'
		      AND post_author = %d
		) AS p
		INNER JOIN {$wpdb->postmeta} AS pm
		ON p.ID = pm.post_id AND pm.meta_key = '_current_pv'
		ORDER BY pm.meta_value + 0 DESC
		LIMIT 0,%d
SQL;
	return array_map( function ( $row ) {
		return new WP_Post( $row );
	}, $wpdb->get_results( $wpdb->prepare( $query, $post->post_author, $limit ) ) );
}

/**
 * 作者の最近の人気作を取得する
 *
 * @param null $post
 * @param int $limit
 * @return WP_Post[]
 */
function hametuha_get_author_recent_works( $post = null, $limit = 5 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	global $wpdb;
	$query = <<<SQL
		SELECT * FROM {$wpdb->posts}
		WHERE post_author = %d
		AND post_type   = 'post'
		AND post_status = 'publish'
		ORDER BY post_date DESC
		LIMIT 0,%d
SQL;
	return array_map( function ( $row ) {
		return new WP_Post( $row );
	}, $wpdb->get_results( $wpdb->prepare( $query, $post->post_author, $limit ) ) );
}

/**
 * その作品の前後の記事を出す
 *
 * @param int              $limit  2の倍数。
 * @param int|null|WP_Post $post   投稿オブジェクト。
 * @param bool             $series 連載の場合は親投稿で縛りつつ、orderも変更する
 * @return WP_Post[]
 */
function hametuha_get_author_work_siblings( $limit = 6, $post = null, $series = false ) {
	$post = get_post( $post );
	if ( 0 !== $limit % 2 ) {
		++$limit;
	}
	$found = [
		'before' => [],
		'after'  => [],
	];
	foreach ( [
		'before' => 'DESC',
		'after'  => 'ASC',
	] as $param => $order ) {
		$args = [
			'post_type'        => $post->post_type,
			'post_status'      => 'publish',
			'author'           => $post->post_author,
			'post__not_in'     => [ $post->ID ],
			'posts_per_page'   => $limit,
			'orderby'          => [ 'date' => $order ],
			'date_query'       => [
				[
					$param => mysql2date( 'Y-m-d H:i', $post->post_date ),
				],
			],
			'suppress_filters' => false,
			'no_found_rows'    => true,
		];
		if ( $series ) {
			$args['post_parent'] = $post->post_parent;
			unset( $args['author'] );
			$args['orderby'] = [
				'menu_order' => ( 'ASC' === $order ) ? 'DESC' : 'ASC',
				'post_date'  => $order,
			];
		}
		$found[ $param ] = get_posts( $args );
	}
	$posts = [];
	if ( $series ) {
		$posts[] = $post;
	}
	for ( $i = 0; $i < $limit; $i++ ) {
		if ( count( $posts ) >= $limit ) {
			break;
		}
		if ( isset( $found['before'][ $i ] ) ) {
			$posts[] = $found['before'][ $i ];
		}
		if ( isset( $found['after'][ $i ] ) ) {
			array_unshift( $posts, $found['after'][ $i ] );
		}
	}
	return $posts;
}

/**
 * ランキングページか否か
 *
 * @param string $type
 * @return bool
 */
function is_ranking( $type = '' ) {
	$ranking = get_query_var( 'ranking' );
	if ( $ranking ) {
		switch ( $type ) {
			case 'yearly':
			case 'monthly':
			case 'daily':
			case 'weekly':
			case 'top':
			case 'best':
			case 'last_week':
				return $type == $ranking;
			default:
				if ( empty( $type ) ) {
					return true;
				} else {
					return false;
				}
		}
	} else {
		return false;
	}
}


/**
 * ランキングのクラスを返す
 *
 * @param int $rank
 * @return string
 */
function ranking_class( $rank ) {
	switch ( $rank ) {
		case 1:
			return ' king';
			break;
		case 2:
		case 3:
			return ' ranker';
			break;
		default:
			return ' normal';
			break;
	}
}

/**
 * 確定済みのランキングか否か
 *
 * @return bool
 */
function is_fixed_ranking() {
	if ( is_ranking( 'yearly' ) ) {
		return get_query_var( 'year' ) < date_i18n( 'Y' );
	} elseif ( is_ranking( 'monthly' ) ) {
		// 現在の日時が翌月3日以降かをチェック
		return current_time( 'timestamp' ) > strtotime( sprintf( '%d-%02d-03 00:00:00', get_query_var( 'year' ), ( get_query_var( 'monthnum' ) + 1 ) ) );
	} elseif ( is_ranking( 'weekly' ) ) {
		// 指定された曜日が最終日曜日よりも前か否か
		return strtotime( sprintf( '%d-%02d-%02d 00:00:00', get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) ) ) <= strtotime( 'Previous Sunday', strtotime( 'Previous Thursday', current_time( 'timestamp' ) ) );
	} elseif ( is_ranking( 'daily' ) ) {
		// 基本OK
		return current_time( 'timestamp' ) > strtotime( sprintf( '%d-%02d-%02d 00:00:00', get_query_var( 'year' ), get_query_var( 'monthnum' ), ( get_query_var( 'day' ) + 3 ) ) );
	} else {
		return false;
	}
}

/**
 * ランキングのタイトルを返す
 *
 * @return string
 */
function ranking_title() {
	switch ( get_query_var( 'ranking' ) ) {
		case 'yearly':
			return sprintf( '%d年のランキング', get_query_var( 'year' ) );
		case 'monthly':
			return sprintf( '%d年%d月のランキング', get_query_var( 'year' ), get_query_var( 'monthnum' ) );
		case 'daily':
			return sprintf( '%d年%d月%d日のランキング', get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		case 'weekly':
			return sprintf( '%d年%d月%d日までの週間ランキング', get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		case 'last_week':
			return __( '先週のランキング', 'hametuha' );
		case 'best':
			$title = '歴代ベスト';
			if ( $slug = get_query_var( 'category_name' ) ) {
				$cat    = get_category_by_slug( $slug );
				$title .= sprintf( '（%s部門）', esc_html( $cat->name ) );
			}
			return $title;
		case 'top':
			return '厳粛なランキング';
		default:
			return 'ランキング';
	}
}
