<?php
/**
 * 計算関係の関数
 *
 * @package hametuha
 */

/**
 * 日数の違いを返す
 *
 * @param null $post
 *
 * @return int
 */
function hametuha_date_diff( $post = null ) {
	$post = get_post( $post );
	return ceil( ( current_time( 'timestamp', true ) - strtotime( $post->post_date_gmt ) ) / 86400 );
}

/**
 * Get formatted string how old this post is.
 *
 * @param int  $limit
 * @param null $post
 * @return string
 */
function hametuha_date_diff_formatted( $limit = 0, $post = null ) {
	$diff = hametuha_date_diff( $post );
	if ( $limit && $diff <= $limit ) {
		// Diff is less than limit.
		return '';
	}
	if ( $diff < 365 ) {
		return sprintf( '%d日', $diff );
	}
	$year = floor( $diff / 365 );
	$half = $diff % 365 > 180 ? '半' : '';
	return sprintf( '%d年%s', $year, $half );
}

/**
 * Detect if post should display updated.
 *
 * @param int              $days
 * @param null|int|WP_Post $post
 * @return bool
 */
function hametuha_remarkably_updated( $days = 30, $post = null ) {
	$days = max( 1, absint( $days ) );
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	$diff = strtotime( $post->post_modified_gmt ) - strtotime( $post->post_date_gmt );
	return ( 60 * 60 * 24 * $days < $diff );
}

/**
 * Check if post is too old.
 *
 * @param int              $days
 * @param null|int|WP_Post $post
 * @return bool
 */
function hametuha_remarkably_old( $days = 30, $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return false;
	}
	$days         = absint( $days );
	$last_updated = max( strtotime( $post->post_date_gmt ), strtotime( $post->post_modified_gmt ) );
	$diff         = current_time( 'timestamp', true ) - $last_updated;
	if ( 0 > $diff ) {
		return false;
	}
	return 60 * 60 * 24 * $days < $diff;
}

/**
 * 小説の長さの分類を返す
 *
 * 長編は連載なので、例外的な扱いにする。
 *
 * @param bool $include_novel trueにした場合は長編も含む
 * @return array<string, array{lable:string, min:int, max:int}>
 */
function hametuha_story_length_category( $include_novel = false ) {
	$stories = [
		'flash' => [
			'label' => __( '掌編',  'hametuha' ),
			'min' => 0,
			'max' => 2000,
		],
		'short' => [
			'label' => __( '短編',  'hametuha' ),
			'min' => 2000,
			'max' => 16000,
		],
		'novelette' => [
			'label' => __( '中編',  'hametuha' ),
			'min' => 16000,
			'max' => 40000,
		],
		'novella' => [
			'label' => __( '中長編',  'hametuha' ),
			'min' => 40000,
			'max' => 80000,
		],
	];
	if ( $include_novel ) {
		$stories['novel'] = [
			'label' => __( '長編',  'hametuha' ),
			'min' => 80000,
			'max' => 9999999999,
		];
	}
	return $stories;
}

/**
 * 長さのカテゴリーを渡されると最大・最小の範囲で返す
 *
 * 'short', 'novella' => [ [ 'min' => 2000, 'max' => 16000 ], [ 'min' => 40000, 'max' => 80000 ] ]
 * 'flash', 'short' => [ [ 'min' => 0, 'max' => 16000 ] ]
 *
 * @param string[] $length_categories 長さの配列
 *
 * @return array<array{min:int, max:int}> min=>maxからなる配列
 */
function hametuha_length_ranges( $length_categories ) {
	if ( empty( $length_categories ) ) {
		return [];
	}

	// すべてのカテゴリー定義を取得
	$all_categories = hametuha_story_length_category( true );

	// 指定されたカテゴリーのみ抽出し、min値でソート
	$categories_data = [];
	foreach ( $length_categories as $key ) {
		if ( isset( $all_categories[ $key ] ) ) {
			$categories_data[ $key ] = $all_categories[ $key ];
		}
	}

	if ( empty( $categories_data ) ) {
		return [];
	}

	// min値でソート
	uasort( $categories_data, function( $a, $b ) {
		return $a['min'] - $b['min'];
	} );

	// 連続する範囲を統合
	$ranges = [];
	$current_range = null;

	foreach ( $categories_data as $data ) {
		if ( null === $current_range ) {
			// 最初の範囲
			$current_range = [
				'min' => $data['min'],
				'max' => $data['max'],
			];
		} elseif ( $current_range['max'] >= $data['min'] ) {
			// 連続している場合は統合
			$current_range['max'] = max( $current_range['max'], $data['max'] );
		} else {
			// 連続していない場合は現在の範囲を保存して新しい範囲を開始
			$ranges[] = $current_range;
			$current_range = [
				'min' => $data['min'],
				'max' => $data['max'],
			];
		}
	}

	// 最後の範囲を追加
	if ( null !== $current_range ) {
		$ranges[] = $current_range;
	}

	return $ranges;
}
