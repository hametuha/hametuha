<?php

namespace Hametuha\ThePost;


use Hametuha\Master\AnnouncementMeta;
use WPametu\Utility\PostHelper;


/**
 * Class Announcement
 * @package Hametuha\ThePost
 *
 * @property-read string|false $start
 * @property-read string|false $end
 * @property-read string|false $notice
 * @property-read string|false $commit_type
 * @property-read string|false $commit_post_type
 * @property-read string|false $commit_category
 * @property-read string|false $commit_start
 * @property-read string|false $commit_end
 */
class Announcement extends PostHelper {

	use AnnouncementMeta;

	/**
	 * 開催期間が存在するか
	 *
	 * @return bool
	 */
	public function has_period() {
		return ( $this->start || $this->end );
	}

	/**
	 * 告知に期限はあるか
	 *
	 * @return bool
	 */
	public function is_limited() {
		return (bool) get_post_meta( $this->post->ID, self::END_OPTION, true );
	}

	/**
	 * 告知が開催終了したか
	 *
	 * @return bool
	 */
	public function is_expired() {
		return $this->is_limited() && current_time( 'timestamp' ) > strtotime( $this->end );
	}

	/**
	 * 告知に場所があるか否か
	 *
	 * @return bool
	 */
	public function has_place() {
		return (bool) get_post_meta( $this->post->ID, self::PLACE, true );
	}

	/**
	 * Get address string
	 *
	 * @param bool $with_building
	 *
	 * @return mixed|string
	 */
	public function get_address( $with_building = true ) {
		$address = get_post_meta( $this->post->ID, self::ADDRESS, true );
		if ( $with_building ) {
			$address .= ' ' . get_post_meta( $this->post->ID, self::BUILDING, true );
		}

		return $address;
	}

	/**
	 * 告知の開催期限を返す
	 *
	 * @return string
	 */
	public function get_period() {
		$start = $this->start;
		$end   = $this->end;
		$str   = [];
		if ( $start ) {
			$str[] = mysql2date( 'Y年m月d日（D）H:i', $start );
		}
		if ( $end ) {
			$format = ( ( strtotime( $end ) - strtotime( $start ) ) <= 60 * 60 * 24 ) ? 'H:i' : 'Y年m月d日（D）';
			$str[]  = mysql2date( $format, $end );
		}

		return implode( ' 〜 ', $str );
	}

	/**
	 * 募集中のイベントか否か
	 *
	 * @return bool
	 */
	public function can_participate() {
		return (bool) get_post_meta( $this->post->ID, self::COMMIT_TYPE, true );
	}

	/**
	 * 募集期間を返す
	 */
	public function get_participating_period() {
		$end = get_post_meta( $this->post->ID, self::COMMIT_END, true );
		if ( $end ) {
			$start = get_post_meta( $this->post->ID, self::COMMIT_START, true ) ?: $this->post->post_date;

			return mysql2date( 'Y年m月d日（D）', $start ) . ' 〜 ' . mysql2date( 'Y年m月d日（D）', $end );
		} else {
			return '無期限';
		}
	}

	/**
	 * 残り時間
	 *
	 * @return int
	 */
	public function left_second_to_participate() {
		$end = get_post_meta( $this->post->ID, self::COMMIT_END, true );
		if ( $end ) {
			$rest = strtotime( $end ) - current_time( 'timestamp' );
			if ( $rest > 0 ) {
				return $rest;
			} else {
				return 0;
			}
		} else {
			return - 1;
		}
	}

	/**
	 * Get comment ID
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_ticket_id( $user_id ) {
		$comments = new \WP_Comment_Query( [
			'post_id' => $this->post->ID,
			'type'    => 'participant',
			'number'  => 1,
			'status'  => '1',
			'user_id' => $user_id,
		] );
		return $comments->comments ? $comments->comments[0]->comment_ID : 0;
	}

	/**
	 * Search user
	 *
	 * @param null|int $user_id
	 *
	 * @return bool|int
	 */
	public function in_list( $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return false;
		}
		$comments = new \WP_Comment_Query( [
			'post_id'    => $this->post->ID,
			'type'       => 'participant',
			'status'     => 1,
			'number'     => 1,
			'user_id'    => $user_id,
			'meta_query' => [
				[
					'key'   => '_participating',
					'value' => 1,
				],
			],
		] );
		return count( $comments->comments ) ?: false;
	}

	/**
	 * Count participants
	 *
	 * @return int
	 */
	public function participating_count() {
		$comments = new \WP_Comment_Query( [
			'post_id'    => $this->post->ID,
			'type'       => 'participant',
			'status'     => 1,
			'number'     => false,
			'meta_query' => [
				[
					'key'   => '_participating',
					'value' => 1,
				],
			],
		] );
		return count( $comments->comments );
	}

	/**
	 * Get event participants
	 *
	 * @param bool $with_mail Default false
	 * @return array
	 */
	public function get_participants( $with_mail = false ) {
		$comments = new \WP_Comment_Query( [
			'post_id'    => $this->post->ID,
			'type'       => 'participant',
			'status'     => 1,
			'number'     => false,
			'meta_query' => [
				[
					'key'   => '_participating',
					'value' => 1,
				],
			],
		] );
		$return   = [];
		foreach ( $comments->comments as $comment ) {
			if ( $user = $this->get_user_object( $comment, $with_mail ) ) {
				$return[] = $user;
			}
		}
		return $return;
	}

	/**
	 * Get user object
	 *
	 * @param \stdClass|\WP_Comment $comment
	 * @param bool                  $with_mail Default false
	 *
	 * @return array
	 */
	public function get_user_object( $comment, $with_mail = false ) {
		$comment = get_comment( $comment );
		$user    = get_userdata( $comment->user_id );
		if ( ! $user ) {
			return [];
		}
		return [
			'id'     => (int) $user->ID,
			'name'   => $user->display_name,
			'url'    => $user->has_cap( 'edit_posts' ) ? esc_url( home_url( "/doujin/detail/{$user->user_nicename}/" ) ) : '#',
			'avatar' => get_avatar_url( $user->ID ),
			'text'   => $comment->comment_content,
			'mail'   => $with_mail ? $user->user_email : '',
		];
	}

	/**
	 * Get participants comment
	 *
	 * @param null|int $user_id
	 *
	 * @return string
	 */
	public function guest_comment( $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return '';
		}
		$comments = new \WP_Comment_Query( [
			'post_id' => $this->post->ID,
			'type'    => 'participant',
			'number'  => 1,
			'status'  => 1,
			'user_id' => $user_id,
		] );
		return $comments->comments ? $comments->comments[0]->comment_content : '';
	}

	/**
	 * 参加条件を返す
	 *
	 * @return string
	 */
	public function participating_condition() {
		$condition = get_post_meta( $this->post->ID, self::COMMIT_CONDITION, true );
		if ( $condition ) {
			return nl2br( $this->str->auto_link( esc_html( $condition ) ) );
		} else {
			return '特になし';
		}
	}

	/**
	 * 参加費用
	 *
	 * @return string
	 */
	public function participating_cost() {
		$cost = get_post_meta( $this->post->ID, self::COMMIT_COST, true );
		if ( $cost > 0 ) {
			return number_format_i18n( $cost ) . '円';
		} else {
			return '無料';
		}
	}

	/**
	 * 参加定員
	 *
	 * @formatted bool フォーマット済みで返すか否か
	 * @return string|int
	 */
	public function participating_limit( $formatted = true ) {
		$limit = (int) get_post_meta( $this->post->ID, self::COMMIT_LIMIT, true );
		if ( ! $formatted ) {
			return $limit;
		}
		if ( $limit > 0 ) {
			return number_format_i18n( $limit ) . '名';
		} else {
			return '無制限';
		}
	}

	/**
	 * 参加している投稿を返す
	 *
	 * @param null $user_id
	 *
	 * @return array
	 */
	public function get_committed_posts( $user_id = null ) {
		if ( $this->commit_type != 2 ) {
			return [];
		}

		$args = [
			'post_type'        => $this->commit_post_type,
			'post_status'      => 'publish',
			'suppress_filters' => false,
		];
		//〆切の判定
		$args['date_query'] = [
			[ 'after' => $this->commit_start ],
		];
		if ( $this->commit_end ) {
			$args['date_query'][0]['before'] = $this->commit_end;
		}
		//カテゴリー指定がある場合
		$categories = $this->get_required_taxonomies_to_commit();
		if ( ! empty( $categories ) ) {
			$term_ids = [];
			$taxonomy = '';
			foreach ( $categories as $cat ) {
				$taxonomy   = $cat->taxonomy;
				$term_ids[] = (int) $taxonomy->term_id;
			}
			$args['tax_query'] = [
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_ids,
				],
			];
		}
		// ユーザー指定がある
		if ( ! is_null( $user_id ) ) {
			$args['post_author'] = $user_id;
		}

		return get_posts( $args );
	}


	/**
	 * 告知に必要なカテゴリーを返す
	 *
	 * @return array
	 */
	public function get_required_taxonomies_to_commit() {
		global $wpdb;
		$categories = $this->commit_category;
		if ( ! empty( $categories ) ) {
			$categories = implode( ',', array_map( 'intval', $categories ) );
			$sql        = <<<EOS
			SELECT * FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON t.term_id = tt.term_id
			WHERE t.term_id IN ({$categories})
EOS;

			return $wpdb->get_results( $sql );
		} else {
			return array();
		}
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'start':
				$start = get_post_meta( $this->post->ID, self::START_OPTION, true );
				if ( $start ) {
					return $start;
				} elseif ( $this->is_limited() ) {
					return $this->post->post_date;
				} else {
					return false;
				}
				break;
			case 'end':
				return get_post_meta( $this->post->ID, self::END_OPTION, true );
				break;
			case 'notice':
				$notice = get_post_meta( $this->post->ID, self::NOTICE, true );
				if ( $notice ) {
					return nl2br( $this->str->auto_link( esc_html( $notice ) ) );
				} else {
					return '特になし';
				}
				break;
			case 'commit_start':
				$start = get_post_meta( $this->post->ID, self::COMMIT_START, true );

				return $start ?: $this->post->post_date;
				break;
			case 'commit_type':
			case 'commit_post_type':
			case 'commit_category':
			case 'commit_end':
				return get_post_meta( $this->post->ID, constant( self::class . '::' . strtoupper( $name ) ), true );
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
