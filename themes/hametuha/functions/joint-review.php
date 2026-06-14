<?php
/**
 * 合評会（当日採点）用関数
 *
 * 合評会＝採点のある公募（campaign）で行う当日採点のためのヘルパー群。
 * セッション状態と参加者はタームメタに、当日点は JointReview モデルに保存する。
 *
 * @feature-group joint-review
 */

/**
 * 合評会のセッション状態を返す
 *
 * '' = 未開催 / 'open' = 入力受付中 / 'published' = 確定・公開
 *
 * @param WP_Term|int $term
 * @return string
 */
function hametuha_jr_state( $term ) {
	$term_id = is_a( $term, 'WP_Term' ) ? $term->term_id : (int) $term;
	$state   = get_term_meta( $term_id, '_jr_state', true );
	return in_array( $state, [ 'open', 'published' ], true ) ? $state : '';
}

/**
 * 合評会のセッション状態を保存する
 *
 * @param int    $term_id
 * @param string $state '', 'open', 'published'
 * @return bool
 */
function hametuha_jr_set_state( $term_id, $state ) {
	if ( ! in_array( $state, [ '', 'open', 'published' ], true ) ) {
		return false;
	}
	return (bool) update_term_meta( (int) $term_id, '_jr_state', $state );
}

/**
 * 当日参加者の user_id 配列を返す
 *
 * @param WP_Term|int $term
 * @return int[]
 */
function hametuha_jr_participants( $term ) {
	$term_id = is_a( $term, 'WP_Term' ) ? $term->term_id : (int) $term;
	$ids     = get_term_meta( $term_id, '_jr_participants', true );
	if ( ! is_array( $ids ) ) {
		return [];
	}
	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
}

/**
 * 当日参加者を保存する
 *
 * @param int   $term_id
 * @param int[] $ids
 * @return bool
 */
function hametuha_jr_set_participants( $term_id, $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $ids ) ) ) );
	return (bool) update_term_meta( (int) $term_id, '_jr_participants', $ids );
}

/**
 * 持ち点（= 参加人数）を返す
 *
 * @param WP_Term|int $term
 * @return int
 */
function hametuha_jr_allotment( $term ) {
	return count( hametuha_jr_participants( $term ) );
}

/**
 * ユーザーが当日参加者か
 *
 * @param WP_Term|int $term
 * @param null|int    $user_id
 * @return bool
 */
function hametuha_jr_is_participant( $term, $user_id = null ) {
	if ( is_null( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	return in_array( (int) $user_id, hametuha_jr_participants( $term ), true );
}

/**
 * 作品（campaign の公開投稿）を ID 昇順で返す
 *
 * @param WP_Term|int $term
 * @return array post_id => author_id
 */
function hametuha_jr_works( $term ) {
	$term_id = is_a( $term, 'WP_Term' ) ? $term->term_id : (int) $term;
	$works   = [];
	foreach ( get_posts( [
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
		'orderby'        => [ 'ID' => 'ASC' ],
		'tax_query'      => [
			[
				'taxonomy' => 'campaign',
				'field'    => 'term_id',
				'terms'    => $term_id,
			],
		],
	] ) as $post ) {
		$works[ (int) $post->ID ] = (int) $post->post_author;
	}
	return $works;
}

/**
 * あるユーザーの当日点の配分を返す
 *
 * @param WP_Term|int $term
 * @param int         $user_id
 * @return array post_id => point
 */
function hametuha_jr_user_distribution( $term, $user_id ) {
	$works = hametuha_jr_works( $term );
	$dist  = array_fill_keys( array_keys( $works ), 0.0 );
	if ( ! $works ) {
		return $dist;
	}
	$model = \Hametuha\Model\JointReview::get_instance();
	foreach ( $model->get_user_points( $user_id, array_keys( $works ) ) as $row ) {
		$dist[ (int) $row->post_id ] = (float) $row->point;
	}
	return $dist;
}

/**
 * 当日点を入力したユーザーID一覧を返す
 *
 * @param WP_Term|int $term
 * @return int[]
 */
function hametuha_jr_voters( $term ) {
	$works = hametuha_jr_works( $term );
	if ( ! $works ) {
		return [];
	}
	return \Hametuha\Model\JointReview::get_instance()->voters( array_keys( $works ) );
}

/**
 * 当日点の配分を検証する
 *
 * @param WP_Term|int $term
 * @param int         $user_id
 * @param array       $distribution post_id => point
 * @return true|WP_Error
 */
function hametuha_jr_validate_distribution( $term, $user_id, $distribution ) {
	$term  = get_term( is_a( $term, 'WP_Term' ) ? $term->term_id : $term, 'campaign' );
	$error = new WP_Error();
	if ( ! $term || is_wp_error( $term ) ) {
		$error->add( 'jr_invalid', __( '公募が見つかりません。', 'hametuha' ) );
		return $error;
	}
	if ( ! hametuha_jr_is_participant( $term, $user_id ) ) {
		$error->add( 'jr_not_participant', __( '採点に参加できるのは当日参加者のみです。', 'hametuha' ) );
		return $error;
	}
	$works     = hametuha_jr_works( $term );
	$allotment = hametuha_jr_allotment( $term );
	$sum       = 0.0;
	foreach ( $distribution as $post_id => $point ) {
		$post_id = (int) $post_id;
		$point   = (float) $point;
		if ( ! isset( $works[ $post_id ] ) ) {
			$error->add( 'jr_invalid_post', __( '対象外の作品が含まれています。', 'hametuha' ) );
			continue;
		}
		if ( $point < 0 ) {
			$error->add( 'jr_negative', __( 'マイナスの点はつけられません。', 'hametuha' ) );
		}
		if ( $works[ $post_id ] === (int) $user_id && $point > 0 ) {
			$error->add( 'jr_own_work', __( '自分の作品には点を入れられません。', 'hametuha' ) );
		}
		$sum += $point;
	}
	// 合計はちょうど持ち点（小数のため誤差を許容）。
	if ( abs( $sum - $allotment ) > 0.001 ) {
		$error->add( 'jr_sum', sprintf(
			/* translators: %1$s: allotment, %2$s: current sum. */
			__( '持ち点 %1$s をちょうど使い切ってください（現在 %2$s）。', 'hametuha' ),
			number_format_i18n( $allotment ),
			rtrim( rtrim( number_format( $sum, 2 ), '0' ), '.' )
		) );
	}
	return $error->has_errors() ? $error : true;
}

/**
 * 当日点の配分を保存する（既存をいったん消して入れ直す）
 *
 * @param WP_Term|int $term
 * @param int         $user_id
 * @param array       $distribution post_id => point
 * @return true|WP_Error
 */
function hametuha_jr_save_distribution( $term, $user_id, $distribution ) {
	$valid = hametuha_jr_validate_distribution( $term, $user_id, $distribution );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}
	$works = hametuha_jr_works( $term );
	$model = \Hametuha\Model\JointReview::get_instance();
	$model->delete_user_points( $user_id, array_keys( $works ) );
	foreach ( $distribution as $post_id => $point ) {
		$point   = (float) $point;
		$post_id = (int) $post_id;
		if ( $point > 0 && isset( $works[ $post_id ] ) ) {
			$model->set_point( $user_id, $post_id, $point );
		}
	}
	$term_id = is_a( $term, 'WP_Term' ) ? $term->term_id : (int) $term;
	wp_cache_delete( $term_id, 'campaign_record' );
	return true;
}

/**
 * 合評会の最終結果を返す（事前点数 + 当日点）
 *
 * @param WP_Term|int $term
 * @return array|WP_Error {
 *   @type array $works        post_id => author_id
 *   @type int   $allotment    持ち点
 *   @type array $participants user_id => [ post_id => point ]（当日点）
 *   @type array $pre          post_id => 事前点数
 *   @type array $live         post_id => 当日点合計
 *   @type array $result       post_id => 事前 + 当日
 * }
 */
function hametuha_joint_review_result( $term ) {
	$term = get_term( is_a( $term, 'WP_Term' ) ? $term->term_id : $term, 'campaign' );
	if ( ! $term || is_wp_error( $term ) ) {
		return new WP_Error( 'jr_invalid', __( '公募が見つかりません。', 'hametuha' ) );
	}
	$works = hametuha_jr_works( $term );
	// 事前点数（content-campaign.php と同じ按分計算）。
	$pre    = array_fill_keys( array_keys( $works ), 0.0 );
	$record = hametuha_campaign_record( $term );
	if ( $record && ! is_wp_error( $record ) && ! empty( $record['participants'] ) ) {
		foreach ( $record['participants'] as $var ) {
			if ( empty( $var['rate_total'] ) || $var['rate_total'] <= 0 ) {
				continue;
			}
			foreach ( $works as $post_id => $author ) {
				$rec              = isset( $var['records'][ $post_id ] ) ? $var['records'][ $post_id ] : 0;
				$pre[ $post_id ] += ( $var['comment_total'] + 1 ) * $rec / $var['rate_total'];
			}
		}
	}
	// 当日点。
	$participants = [];
	foreach ( hametuha_jr_participants( $term ) as $uid ) {
		$participants[ $uid ] = array_fill_keys( array_keys( $works ), 0.0 );
	}
	$live = array_fill_keys( array_keys( $works ), 0.0 );
	if ( $works ) {
		$model = \Hametuha\Model\JointReview::get_instance();
		foreach ( $model->get_points_for_posts( array_keys( $works ) ) as $row ) {
			$uid = (int) $row->user_id;
			$pid = (int) $row->post_id;
			$pt  = (float) $row->point;
			if ( ! isset( $participants[ $uid ] ) ) {
				$participants[ $uid ] = array_fill_keys( array_keys( $works ), 0.0 );
			}
			$participants[ $uid ][ $pid ] = $pt;
			$live[ $pid ]                += $pt;
		}
	}
	$result = [];
	foreach ( $works as $post_id => $author ) {
		$result[ $post_id ] = $pre[ $post_id ] + $live[ $post_id ];
	}
	return [
		'works'        => $works,
		'allotment'    => hametuha_jr_allotment( $term ),
		'participants' => $participants,
		'pre'          => $pre,
		'live'         => $live,
		'result'       => $result,
	];
}
