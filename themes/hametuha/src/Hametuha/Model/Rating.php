<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Class Rank
 *
 * @package Hametuha\Model
 * @feature-group rating
 * @property-read string $posts
 */
class Rating extends Model {

	/**
	 * ユーザーとコンテンツを紐づけるテーブル名
	 *
	 * @var string
	 */
	protected $name = 'user_content_relationships';

	/**
	 *
	 *
	 * @var array
	 */
	protected $related = [ 'posts' ];

	/**
	 * キー名
	 *
	 * @var string
	 */
	protected $type = 'rank';

	/**
	 * Primary key of this table
	 *
	 * @var string
	 */
	protected $primary_key = 'ID';

	/**
	 * @var string
	 */
	protected $updated_column = 'updated';

	/**
	 * @var array
	 */
	protected $default_placeholder = [
		'ID'        => '%d',
		'rel_type'  => '%s',
		'object_id' => '%d',
		'user_id'   => '%d',
		'location'  => '%f',
		'content'   => '%s',
	];

	/**
	 * Update rating
	 *
	 * @param int $rank
	 * @param int $user_id
	 * @param int $post_id
	 *
	 * @return false|int
	 */
	public function update_rating( $rank, $user_id, $post_id ) {
		if ( is_null( $this->get_users_rating( $post_id, $user_id ) ) ) {
			return $this->insert( [
				'rel_type'  => $this->type,
				'object_id' => $post_id,
				'user_id'   => $user_id,
				'location'  => $rank / 10
			] );
		} else {
			return $this->update( [
				'location' => $rank / 10
			], [
				'rel_type'  => $this->type,
				'object_id' => $post_id,
				'user_id'   => $user_id
			] );
		}
	}

	/**
	 * ユーザーのレーティングを削除する
	 *
	 * @param int $user_id
	 * @param int $post_id
	 *
	 * @return boolean
	 */
	public function delete_rating( $user_id, $post_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}
		return (bool) $this->db->delete( $this->table, [
			'rel_type'  => $this->type,
			'object_id' => $post_id,
			'user_id'   => $user_id,
		], [ '%s', '%d', '%d' ] );
	}

	/**
	 * Get user's rating
	 *
	 * @param int $post_id
	 * @param int $user_id
	 *
	 * @return null|int
	 */
	public function get_users_rating( $post_id, $user_id ) {
		if ( ! $user_id ) {
			return null;
		}
		$rank = $this->select( "{$this->table}.location" )
					 ->wheres( [
						 "{$this->table}.rel_type = %s"  => $this->type,
						 "{$this->table}.user_id = %d"   => $user_id,
						 "{$this->table}.object_id = %d" => $post_id,
					 ] )->get_var();
		if ( is_null( $rank ) ) {
			return null;
		} else {
			return (int) floor( $rank * 10 );
		}
	}

	/**
	 * 投稿が取得した☆の平均を返す
	 *
	 * @param \WP_Post|int|\WP_Post $post
	 *
	 * @return null|float
	 */
	function get_post_rating( \WP_Post $post = null ) {
		$post = get_post( $post );
		$this->select( "AVG({$this->table}.location)" )
			 ->where( "{$this->table}.rel_type = %s", $this->type );
		if ( $this->is_series( $post ) ) {
			$this->where( "{$this->posts}.post_parent = %d", $post->ID );
		} else {
			$this->where( "{$this->table}.object_id = %d", $post->ID );
		}
		$avg = $this->get_var();
		if ( is_null( $avg ) ) {
			return null;
		} else {
			return round( $avg * 10, 1 );
		}
	}

	/**
	 * 投稿に付与された評価の件数を返す
	 *
	 * @param \WP_Post|int|\WP_Post $post
	 *
	 * @return int
	 */
	public function get_post_rating_count( \WP_Post $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return 0;
		}
		$this->select( "COUNT({$this->table}.ID)" )
			 ->where( "{$this->table}.rel_type = %s", $this->type );
		if ( $this->is_series( $post ) ) {
			$this->where( "{$this->posts}.post_parent = %d", $post->ID );
		} else {
			$this->where( "{$this->table}.object_id = %d", $post->ID );
		}

		return (int) $this->get_var();
	}

	/**
	 * 投稿の平均星獲得数と総獲得件数を保存する
	 *
	 * @param int $post_id
	 * @return bool 値が更新された場合はtrue、変更がなかった場合はfalse
	 */
	public function update_post_average( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'post' !== $post->post_type ) {
			return false;
		}
		$updated = false;
		// レーティングの平均を取得して、保存する。
		$avg = $this->get_post_rating( $post );
		if ( is_null( $avg ) ) {
			// 平均がない＝レコードがない
			if ( delete_post_meta( $post->ID, '_rating_average' ) ) {
				$updated = true;
			}
		} else {
			if ( update_post_meta( $post->ID, '_rating_average', $avg ) ) {
				$updated = true;
			}
		}
		// レーティングの総数を取得して保存する
		$total = $this->get_post_rating_count( $post );
		if ( update_post_meta( $post->ID, '_rating_count', $total ) ) {
			$updated = true;
		}
		return $updated;
	}

	/**
	 * 投稿に付与された評価のリストを返す
	 *
	 * @param int|array $post_id
	 * @param string $limit
	 *
	 * @return array|mixed|null
	 */
	public function get_user_points( $post_id, $limit = '' ) {
		$this
			->from( $this->table )
			->calc( false )
			->select( 'object_id as post_id, user_id, location AS rating' )
			->where( 'rel_type = %s', $this->type );
		if ( is_array( $post_id ) ) {
			$this->where_in( 'object_id', $post_id, '%d' );
		} else {
			$this->where( 'object_id = %d', $post_id );
		}
		if ( $limit ) {
			$this->where( 'updated <= %s', $limit );
		}

		return $this->result();
	}

	/**
	 * If this is series?
	 *
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function is_series( \WP_Post $post ) {
		return 'series' === $post->post_type;
	}


	/**
	 * Default join
	 *
	 * @return array
	 */
	protected function default_join() {
		return [
			[ $this->posts, "{$this->posts}.ID = {$this->table}.object_id", 'inner' ],
		];
	}

	/**
	 * Get reviewed posts.
	 *
	 * @param int $user_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_my_reviewed_posts( $user_id, $args = [] ) {
		$args           = wp_parse_args( $args, [
			'paged'          => 1,
			'posts_per_page' => 20,
		] );
		$paged          = $args[ 'paged' ];
		$posts_per_page = $args[ 'posts_per_page' ];
		$offset         = ( max( 1, $paged ) - 1 ) * $posts_per_page;
		$wheres         = [
			'r.rel_type = "rank"',
			$this->db->prepare( 'p.post_author = %d', $user_id ),
			'p.post_type = "post"',
		];
		$wheres         = implode( ' AND ', $wheres );
		$sql            = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS
		    	p.*, r.location as rating, r.user_id as reviewer, r.updated as reviewed_at
			FROM {$this->table} as r
			LEFT JOIN {$this->db->posts} as p
			ON p.ID = r.object_id
			WHERE {$wheres}
			ORDER BY r.updated DESC
			LIMIT %d, %d
SQL;
		$result         = $this->db->get_results( $this->db->prepare( $sql, $offset, $posts_per_page ) );
		$found          = (int) $this->db->get_var( 'SELECT FOUND_ROWS()' );

		return [
			'found'   => $found,
			'current' => $paged,
			'total'   => ceil( $found / $posts_per_page ),
			'reviews' => array_map( function ( $post ) {
				return new \WP_Post( $post );
			}, $result ),
		];
	}

	/**
	 * Get reviewed posts.
	 *
	 * @param int $user_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_reviewed_posts_by( $user_id, $args = [] ) {
		$args           = wp_parse_args( $args, [
			'paged'          => 1,
			'posts_per_page' => 20,
		] );
		$paged          = $args[ 'paged' ];
		$posts_per_page = $args[ 'posts_per_page' ];
		$offset         = ( max( 1, $paged ) - 1 ) * $posts_per_page;
		$wheres         = [
			'r.rel_type = "rank"',
			$this->db->prepare( 'r.user_id = %d', $user_id ),
			'p.post_type = "post"',
			'p.post_status = "publish"',
		];
		$wheres         = implode( ' AND ', $wheres );
		$sql            = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS
		    	p.*, r.location as rating, r.user_id as reviewer, r.updated as reviewed_at
			FROM {$this->table} as r
			LEFT JOIN {$this->db->posts} as p
			ON p.ID = r.object_id
			WHERE {$wheres}
			ORDER BY r.updated DESC
			LIMIT %d, %d
SQL;
		$result         = $this->db->get_results( $this->db->prepare( $sql, $offset, $posts_per_page ) );
		$found          = (int) $this->db->get_var( 'SELECT FOUND_ROWS()' );

		return [
			'found'   => $found,
			'current' => $paged,
			'total'   => ceil( $found / $posts_per_page ),
			'reviews' => array_map( function ( $post ) {
				return new \WP_Post( $post );
			}, $result ),
		];
	}
}
