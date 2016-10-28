<?php
/**
 * キャンペーン用ファイル
 */


/**
 * キャンペーンの採点結果を表示する
 *
 * @param null|WP_Term $term
 *
 * @return array|WP_Error
 */
function hametuha_campaign_record( $term = null ) {
	if ( is_null( $term ) && is_tax( 'campaign' ) ) {
		$term = get_queried_object();
	}
	if ( ! $term || 'campaign' !== $term->taxonomy ) {
		return [];
	}
	$limit = get_term_meta( $term->term_id, '_campaign_range_end', true );
	if ( $limit ) {
		$limit .= ' 23:59:59';
	} else {
		return new WP_Error( 'not_campaign', sprintf( '%sは採点のないキャンペーンです。', $term->name ) );
	}
	$record = wp_cache_get( $term->term_id, 'campaign_record' );
	if ( false === $record ) {
		$post_ids = [];
		$participants = [];
		$record_base = [];
		// Get submitted posts
		foreach ( get_posts( [
			'posts_per_page' => -1,
		    'post_status' => 'publish',
		    'no_found_rows' => true,
		    'orderby' => [
		    	'ID' => 'ASC',
		    ],
		    'tax_query' => [
		    	[
		    		'taxonomy' => 'campaign',
			        'field'   => 'id',
			        'terms'    => $term->term_id,
			    ],
		    ],
		] ) as $post ) {
			$participants[ (int) $post->post_author ] = [
				'author' => true,
			    'comment_total'  => 0,
			    'rate_total' => 0,
			];
			$post_ids[ (int) $post->ID ] = (int) $post->post_author;
			$record_base[ (int) $post->ID ] = 0;
		}
		if ( ! $post_ids ) {
			return [];
		}
		// Get records
		$model = \Hametuha\Model\Rating::get_instance();
		$records = $model->get_user_points( array_keys( $post_ids ), $limit );
		// Get all participants
		foreach ( $records as $record ) {
			if ( ! array_key_exists( (int) $record->user_id, $participants ) ) {
				$participants[ (int) $record->user_id ] = [
					'author'        => false,
					'comment_total' => 0,
					'rate_total'    => 0,
				];
			}
		}
		// Calculate comment total
		foreach ( hametuha_comment_point( array_keys( $post_ids ) ) as $row ) {
			$participants[ $row->user_id ]['comment_total'] = (int) $row->comment_count;
		}
		// Add base record
		foreach ( $participants as $id => $vars ) {
			$participants[ $id ]['records'] = $record_base;
		}
		// Calculate record total
		foreach ( $records as $record ) {
			$score = intval( 10 * $record->rating );
			$participants[ $record->user_id ]['rate_total'] += $score;
			$participants[ $record->user_id ]['records'][ $record->post_id ] = $score;
		}
		$record = [
			'posts'        => $post_ids,
		    'participants' => $participants,
		];
		wp_cache_set( $term->term_id, $record, 'campaign_record' );
	}
	return $record;
}

/**
 * Get comment count
 *
 * @param array $post_ids
 *
 * @return array|null|object
 */
function hametuha_comment_point( $post_ids ) {
	global $wpdb;
	$in = implode( ', ', array_map( 'intval', $post_ids ) );
	$query = <<<SQL
		SELECT user_id, COUNT( DISTINCT comment_post_ID ) AS comment_count
		FROM {$wpdb->comments}
		WHERE comment_post_ID IN ({$in})
		  AND user_id > 0
		GROUP BY user_id
SQL;
	return $wpdb->get_results( $query );
}


/**
 * 合評会のタームを返す
 *
 * @param int  $year
 * @param bool $ascendant
 *
 * @return array
 */
function hametuha_review_terms( $year, $ascendant = true ) {
	$terms = get_terms( [
		'taxonomy' => 'campaign',
	    'hide_empty' => false,
	] );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return [];
	}
	$terms = array_filter( $terms, function ( $term ) use ( $year ) {
		$first     = implode( '|', array_map( function( $m ) {
			return sprintf( '%02d', $m );
		}, range( 4, 12 ) ) );
		$second    = implode( '|', array_map( function( $m ) {
			return sprintf( '%02d', $m );
		}, range( 1, 3 ) ) );
		$next_year = $year + 1;
		return preg_match( "#^joint-review-{$year}({$first})$#", $term->slug ) || preg_match( "#^joint-review-{$next_year}({$second})$#", $term->slug );
	} );
	usort( $terms, function( $a, $b ) use ( $ascendant ) {
		if ( $a->slug == $b->slug ) {
			return 0;
		} else {
			if ( $ascendant ) {
				return $a->slug > $b->slug ? 1 : -1;
			} else {
				return $a->slug > $b->slug ? -1 : 1;
			}
		}
	} );
	return $terms;
}

/**
 * キャンペーンが応募中か
 *
 * @param WP_Term $term
 * @param string $when
 *
 * @return bool
 */
function hametuha_is_available_campaign( $term, $when = 'now' ) {
	$tz   = new DateTimeZone( 'Asia/Tokyo' );
	$time = new DateTime( $when, $tz );
	if ( ! $limit = get_term_meta( $term->term_id, '_campaign_limit', true ) ) {
		return true;
	}
	$limit = new DateTime( $limit . ' 23:59:59', $tz );

	return ( $limit >= $time );
}

/**
 * Detect if term has limit.
 *
 * @param int $term_id
 *
 * @return bool
 */
function hametuha_campaign_has_limit( $term_id ) {
	$date = get_term_meta( $term_id, '_campaign_limit', true );
	return (bool) preg_match( '#^\d{4}-\d{2}-\d{2}$#', $date );
}

/**
 * 文字数の制限を出力する
 *
 * @param int|string|WP_Term $term
 * @param string $format
 *
 * @return bool|string
 */
function hametuha_campaign_length( $term, $format = 'paper' ) {
	$term = get_term( $term, 'campaign' );
	if ( ! $term || is_wp_error( $term ) ) {
		return false;
	}
	$formatter = function( $number, $min = true ) use ( $format ) {
		switch ( $format ) {
			case 'paper':
				$return = sprintf( '%s枚', number_format( $number / 400 ) );
				break;
			default:
				$return = sprintf( '%s文字', number_format( $number ) );
				break;
		}
		return $return . ( $min ? '以上' : '以下' );
	};
	$return = '';
	if ( $min = get_term_meta( $term->term_id, '_campaign_min_length', true ) ) {
		$return .= $formatter( $min );
	}
	if ( $max = get_term_meta( $term->term_id, '_campaign_max_length', true ) ) {
		$return .= $formatter( $max, false );
	}
	if ( 'paper' == $format && $return ) {
		$return = '400字詰原稿用紙'.$return;
	}
	return $return;
}

/**
 * キャンペーンとして有効か否かを返す
 *
 * @param int $campaign_id
 * @param null|int|WP_Post $post
 *
 * @return WP_Error|true
 */
function hametuha_valid_for_campaign( $campaign_id, $post = null ) {
	$post = get_post( $post );
	$campaign = get_term_by( 'id', $campaign_id, 'campaign' );
	$error = new WP_Error();
	if ( ! $campaign ) {
		$error->add( 404, '該当するキャンペーンが存在しません。' );
		return $error;
	}
	if ( hametuha_campaign_has_limit( $campaign_id ) ) {
		if ( ( false !== array_search( $post->post_status, [ 'future', 'publish', 'private' ] ) )
		     && ! hametuha_is_available_campaign( $campaign, $post->post_date )
		) {
			$error->add( '500', '応募期限を過ぎています。' );
		}
	}
	$min = get_term_meta( $campaign_id, '_campaign_min_length' );
	if ( $min && ( mb_strlen( strip_tags( $post->post_content ) ) < $min ) ) {
		$error->add( 500, '最低応募文字数に達していません。' );
	}
	$max = get_term_meta( $campaign_id, '_campaign_max_length' );
	if ( $max && ( mb_strlen( strip_tags( $post->post_content ) ) > $max ) ) {
		$error->add( 500, '文字数が長すぎます。' );
	}
	return $error->get_error_messages() ? $error : true;
}
