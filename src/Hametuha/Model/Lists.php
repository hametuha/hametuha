<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Class Lists
 *
 * @package Hametuha\Model
 *
 * @property-read string $posts
 */
class Lists extends Model {


	/**
	 * リコメンドのメタキー
	 *
	 * @const
	 */
	const META_KEY_RECOMMEND = '_recommended_list';

	/**
	 * コンテンツとコンテンツを紐づけるテーブル
	 *
	 * @var string
	 */
	protected $name = 'object_relationships';

	/**
	 * 関連するテーブル
	 *
	 * @var array
	 */
	protected $related = [ 'posts' ];

	/**
	 * キー名
	 *
	 * @var string
	 */
	protected $type = 'list';

	/**
	 * Primary key of this table
	 *
	 * @var string
	 */
	protected $primary_key = 'ID';

	/**
	 * @var array
	 */
	protected $default_placeholder = [
		'ID'         => '%d',
		'rel_type'   => '%s',
		'subject_id' => '%d',
		'object_id'  => '%d',
		'created'    => '%d',
	];


	/**
	 * ユーザーがリストを扱うことが可能か
	 *
	 * @param int $list_id
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function user_can( $list_id, $user_id ) {
		$list = get_post( $list_id );
		if ( $list->post_author == $user_id ) {
			return true;
		} else {
			$user = new \WP_User( $user_id );
			if ( $user->has_cap( 'edit_others_posts' ) ) {
				return true;
			} else {
				// TODO: 公開リストの扱いなど
				return false;
			}
		}
	}


	/**
	 * 投稿を保存する
	 *
	 * @param int $list_id
	 * @param array $post_ids
	 *
	 * @return array
	 */
	public function save( $list_id, array $post_ids ) {
		$total = count( $post_ids );
		$added = 0;
		// ないヤツを追加
		foreach ( $post_ids as $post_id ) {
			if ( ! $this->exists_in( $list_id, $post_id ) ) {
				$this->insert(
					[
						'rel_type'   => 'list',
						'subject_id' => $list_id,
						'object_id'  => $post_id,
						'created'    => current_time( 'timestamp' ),
					]
				);
				$added++;
			}
		}
		// 指定したものの中になければ削除
		$deleted = (int) $this->delete_not_in( $post_ids, $list_id );
		// 配列を返す
		return compact( 'total', 'added', 'deleted' );
	}

	/**
	 * 一括登録
	 *
	 * @param int $post_id
	 * @param array $list_ids
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function bulk_register( $post_id, array $list_ids ) {
		// 新規追加
		foreach ( $list_ids as $list_id ) {
			if ( ! $this->exists_in( $list_id, $post_id ) ) {
				$this->insert(
					[
						'rel_type'   => 'list',
						'subject_id' => $list_id,
						'object_id'  => $post_id,
						'created'    => current_time( 'timestamp' ),
					]
				);
			}
		}
		// リストが指定されていない場合は削除
		$this->delete_where(
			[
				[ 'rel_type', '=', 'list', '%s' ],
				[ 'subject_id', 'NOT IN', $list_ids, '%d' ],
				[ 'object_id', '=', $post_id, '%d' ],
			]
		);
		return count( $list_ids );
	}

	/**
	 * リストから登録を解除する
	 *
	 * @param int $list_id
	 * @param int $post_id
	 *
	 * @return false|int
	 * @throws \Exception
	 */
	public function deregister( $list_id, $post_id ) {
		return $this->delete_where(
			[
				[ 'rel_type', '=', 'list', '%s' ],
				[ 'subject_id', '=', $list_id, '%d' ],
				[ 'object_id', '=', $post_id, '%d' ],
			]
		);
	}


	/**
	 * リストの中に指定した投稿が存在するか
	 *
	 * @param int $list_id
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function exists_in( $list_id, $post_id ) {
		return (bool) $this->wheres(
			[
				'rel_type = %s'   => 'list',
				'subject_id = %d' => $list_id,
				'object_id = %d'  => $post_id,
			]
		)->get_row();
	}

	/**
	 * リストから指定された物以外を削除する
	 *
	 * @param array $post_ids
	 * @param int $list_id
	 *
	 * @return false|int
	 * @throws \Exception
	 */
	protected function delete_not_in( array $post_ids, $list_id ) {
		$wheres = [
			[ 'rel_type', '=', 'list', '%s' ],
			[ 'subject_id', '=', $list_id, '%d' ],
		];
		if ( ! empty( $post_ids ) ) {
			$wheres[] = [ 'object_id', 'NOT IN', $post_ids, '%d' ];

		}
		return $this->delete_where( $wheres );
	}

	/**
	 * ひも付けられた投稿の数を出力する
	 *
	 * @param array $post_ids
	 *
	 * @return array|mixed|null
	 */
	public function num_children( $post_ids ) {
		if ( is_numeric( $post_ids ) ) {
			$post_ids = [ $post_ids ];
		} else {
			$post_ids = (array) $post_ids;
		}
		return $this->select( 'subject_id, COUNT(object_id) AS count' )
			->where( 'rel_type = %s', 'list' )
			->where_in( 'subject_id', $post_ids, '%d' )
			->group_by( 'subject_id' )->result();
	}

	/**
	 * 指定されたリストの件数を返す
	 *
	 * @param int $list_id
	 *
	 * @return int
	 */
	public function count( $list_id ) {
		return (int) $this->select( 'COUNT(object_id)' )
			->where( 'rel_type = %s', 'list' )
			->where( 'subject_id = %d', $list_id )
			->get_var();
	}

	/**
	 * お勧めにする
	 *
	 * @param int $post_id
	 */
	public function mark_as_recommended( $post_id ) {
		update_post_meta( $post_id, self::META_KEY_RECOMMEND, 1 );
	}

	/**
	 * お勧めではなくする
	 *
	 * @param int $post_id
	 */
	public function not_recommended( $post_id ) {
		delete_post_meta( $post_id, self::META_KEY_RECOMMEND );
	}

	/**
	 * 投稿がお勧めか否か
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_recommended( $post_id ) {
		return (bool) get_post_meta( $post_id, self::META_KEY_RECOMMEND, true );
	}

	/**
	 * 投稿を削除する
	 *
	 * @param int $post_id
	 *
	 * @return false|int
	 * @throws \Exception
	 */
	public function clear_relation( $post_id ) {
		return $this->delete_where(
			[
				[ 'rel_type', '=', 'list', '%s' ],
				[ 'subject_id', '=', $post_id, '%d' ],
			]
		);
	}

}
