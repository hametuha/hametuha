<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * 合評会の当日点モデル
 *
 * Rating と同じ user_content_relationships テーブルを rel_type='jr_point' で再利用する。
 * location カラムは decimal(10,9)（整数部1桁）なので、Rating と同様に配点を 1/10 にして
 * 保存し、読み出し時に 10 倍する（持ち点が 9 を超えても溢れないようにするため）。
 *
 * @package Hametuha\Model
 * @feature-group joint-review
 * @property-read string $posts
 */
class JointReview extends Model {

	/**
	 * ユーザーとコンテンツを紐づけるテーブル名
	 *
	 * @var string
	 */
	protected $name = 'user_content_relationships';

	/**
	 * レコード種別
	 *
	 * @var string
	 */
	protected $type = 'jr_point';

	/**
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
	 * 配点を保存する（なければ追加、あれば更新）
	 *
	 * @param int   $user_id
	 * @param int   $post_id
	 * @param float $point
	 * @return false|int
	 */
	public function set_point( $user_id, $post_id, $point ) {
		$where  = [
			'rel_type'  => $this->type,
			'object_id' => $post_id,
			'user_id'   => $user_id,
		];
		$exists = $this->select( "{$this->table}.ID" )
			->wheres( [
				"{$this->table}.rel_type = %s"  => $this->type,
				"{$this->table}.user_id = %d"   => $user_id,
				"{$this->table}.object_id = %d" => $post_id,
			] )->get_var();
		if ( is_null( $exists ) ) {
			return $this->insert( array_merge( $where, [
				'location' => $point / 10,
				'content'  => '',
			] ) );
		}
		return $this->update( [ 'location' => $point / 10 ], $where );
	}

	/**
	 * あるユーザーの配点を取得する
	 *
	 * @param int   $user_id
	 * @param int[] $post_ids
	 * @return object[] {post_id, point}
	 */
	public function get_user_points( $user_id, $post_ids ) {
		$post_ids = array_map( 'intval', (array) $post_ids );
		if ( ! $post_ids ) {
			return [];
		}
		$this->select( 'object_id AS post_id, location * 10 AS point' )
			->where( 'rel_type = %s', $this->type )
			->where( 'user_id = %d', $user_id )
			->where_in( 'object_id', $post_ids, '%d' );
		return $this->result();
	}

	/**
	 * 作品群につけられた全配点を取得する
	 *
	 * @param int[] $post_ids
	 * @return object[] {user_id, post_id, point}
	 */
	public function get_points_for_posts( $post_ids ) {
		$post_ids = array_map( 'intval', (array) $post_ids );
		if ( ! $post_ids ) {
			return [];
		}
		$this->select( 'user_id, object_id AS post_id, location * 10 AS point' )
			->where( 'rel_type = %s', $this->type )
			->where_in( 'object_id', $post_ids, '%d' );
		return $this->result();
	}

	/**
	 * 配点したユーザーID一覧を返す
	 *
	 * @param int[] $post_ids
	 * @return int[]
	 */
	public function voters( $post_ids ) {
		$post_ids = array_map( 'intval', (array) $post_ids );
		if ( ! $post_ids ) {
			return [];
		}
		$this->select( 'DISTINCT user_id' )
			->where( 'rel_type = %s', $this->type )
			->where_in( 'object_id', $post_ids, '%d' );
		return array_map( 'intval', wp_list_pluck( $this->result(), 'user_id' ) );
	}

	/**
	 * ユーザーの配点を削除する
	 *
	 * @param int   $user_id
	 * @param int[] $post_ids
	 * @return int|false
	 */
	public function delete_user_points( $user_id, $post_ids ) {
		$post_ids = array_map( 'intval', (array) $post_ids );
		if ( ! $post_ids ) {
			return 0;
		}
		$in = implode( ',', $post_ids );
		return $this->db->query( $this->db->prepare(
			"DELETE FROM {$this->table} WHERE rel_type = %s AND user_id = %d AND object_id IN ({$in})",
			$this->type,
			$user_id
		) );
	}

	/**
	 * 作品群の配点を全削除する（リセット用）
	 *
	 * @param int[] $post_ids
	 * @return int|false
	 */
	public function clear_points( $post_ids ) {
		$post_ids = array_map( 'intval', (array) $post_ids );
		if ( ! $post_ids ) {
			return 0;
		}
		$in = implode( ',', $post_ids );
		return $this->db->query( $this->db->prepare(
			"DELETE FROM {$this->table} WHERE rel_type = %s AND object_id IN ({$in})",
			$this->type
		) );
	}
}
