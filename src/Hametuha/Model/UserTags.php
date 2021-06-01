<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

class UserTags extends TermUserRelationships {

	/**
	 * 投稿タグ
	 *
	 * @var string
	 */
	public $taxonomy = 'post_tag';

	/**
	 * 指定した名称のタグのIDを返し、なければ作る
	 *
	 * @param string $term_name
	 *
	 * @return \stdClass
	 */
	public function create_term_if_not_exists( $term_name ) {
		$term = get_term_by( 'name', $term_name, $this->taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		} else {
			$result = wp_insert_term( $term_name, $this->taxonomy );
			if ( is_wp_error( $result ) ) {
				return 0;
			} else {
				return get_term( $result['term_id'], $this->taxonomy );
			}
		}
	}

	/**
	 * ユーザータグを追加する
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 *
	 * @return int 1 if added. 0 on failure, -1 if already exists.
	 */
	public function add_user_tag( $user_id, $post_id, $term_taxonomy_id ) {
		$existence = $this->record_exists( $user_id, $post_id, $term_taxonomy_id );
		if ( ! $existence ) {
			return (int) $this->insert(
				[
					'user_id'          => $user_id,
					'object_id'        => $post_id,
					'term_taxonomy_id' => $term_taxonomy_id,
				]
			);
		} else {
			return - 1;
		}
	}

	/**
	 * ユーザータグを削除する
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 *
	 * @return bool
	 */
	public function remove_user_tag( $user_id, $post_id, $term_taxonomy_id ) {
		return (bool) $this->delete_where(
			[
				[ 'user_id', '=', $user_id, '%d' ],
				[ 'object_id', '=', $post_id, '%d' ],
				[ 'term_taxonomy_id', '=', $term_taxonomy_id, '%d' ],
			]
		);
	}

	/**
	 * ユーザーが投稿に対してつけたタグをすべて返す
	 *
	 * @param int $post_id
	 * @param int $user_id
	 *
	 * @return array
	 */
	public function get_post_tags( $post_id, $user_id = 0 ) {
		return $this->select( "{$this->terms}.*, {$this->term_taxonomy}.*, COUNT({$this->table}.user_id) AS number" )
					->select( sprintf( "SUM({$this->table}.user_id = %d) AS owning", $user_id ) )
					->wheres(
						[
							"{$this->table}.object_id = %d" => $post_id,
							"{$this->term_taxonomy}.taxonomy = %s" => $this->taxonomy,
						]
					)->order_by( "{$this->terms}.name", 'ASC' )
					->group_by( "{$this->table}.term_taxonomy_id" )->result();
	}

	/**
	 * 最新のタグ状況を取得する
	 *
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 * @param int $user_id
	 *
	 * @return mixed|null
	 */
	public function get_latest_tag( $post_id, $term_taxonomy_id, $user_id = 0 ) {
		$row = $this->select( "{$this->terms}.*, {$this->term_taxonomy}.*, COUNT({$this->table}.user_id) AS number" )
					->select( sprintf( "SUM({$this->table}.user_id = %d) AS owning", $user_id ) )
					->wheres(
						[
							"{$this->table}.object_id = %d" => $post_id,
							"{$this->table}.term_taxonomy_id = %d" => $term_taxonomy_id,
						]
					)->group_by( "{$this->table}.term_taxonomy_id" )->get_row( '', true );

		return $row;
	}

	/**
	 * Search tags with query
	 *
	 * @param string $query
	 * @param int $offset
	 * @param int $per_page
	 *
	 * @return array|mixed|null
	 */
	public function tag_search( $query, $offset = 0, $per_page = 10 ) {
		$result = $this->select( "{$this->terms}.*, {$this->term_taxonomy}.*" )
					   ->from( $this->terms )
					   ->join( $this->term_taxonomy, "{$this->terms}.term_id = {$this->term_taxonomy}.term_id", 'INNER' )
					   ->where( "{$this->term_taxonomy}.taxonomy = %s", $this->taxonomy )
					   ->where_like( "{$this->terms}.name", $query )
					   ->order_by( "{$this->terms}.name", 'ASC' )
					   ->limit( $per_page, $offset * $per_page )->result();

		return $result;
	}

}
