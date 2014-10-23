<?php

namespace Hametuha\QueryHighJack;


use Hametuha\Model\Lists;
use WPametu\API\QueryHighJack;


/**
 * 自分のクエリを表示する
 *
 * @package Hametuha\QueryHighJack
 * @property-read Lists $lists
 */
class ListQuery extends QueryHighJack
{

	/**
	 * リストID
	 *
	 * @var int
	 */
	private $list_id = 0;

	/**
	 * 空のリストを除外する
	 *
	 * @var bool
	 */
	private $exclude_empty = false;

	/**
	 * 使用するモデル
	 *
	 * @var array
	 */
	protected $models = [
		'lists' => Lists::class
	];

	/**
	 * @var array
	 */
	protected $query_var = ['my-content', 'in_list'];



	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'^your/lists/?$' => 'index.php?my-content=lists&post_type=lists&post_status=publish,private,future&author=0',
		'^your/lists/paged/([0-9]+)/?$' => 'index.php?my-content=lists&post_type=lists&post_status=publish,private,future&paged=$matches[1]&author=0',
		'^recommends/?$' => 'index.php?my-content=recommends&post_type=lists&post_status=publish',
		'^recommends/paged/([0-9]+)/?$' => 'index.php?my-content=recommends&post_type=lists&post_status=publish&paged=$matches[1]',
	];

	/**
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		return is_user_logged_in() && 'lists' == $wp_query->get('my-content');
	}


	/**
	 * ユーザーIDを指定する
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ){
		// 自分のリストを表示する
		if( $this->is_valid_query($wp_query) ){
			// キャッシュさせない
			if( $wp_query->is_main_query() ){
				nocache_headers();
			}
			// クエリにユーザーIDを追加
			$wp_query->set('author', get_current_user_id());
			$wp_query->set('post_status', ['publish', 'private', 'future']);
		}
		elseif( 'recommends' == $wp_query->get('my-content') ){
			// おすすめの場合はメタクエリ追加
			$wp_query->set('meta_query', [
				[
					'key' => Lists::META_KEY_RECOMMEND,
					'value' => 1,
				]
			]);
			$this->exclude_empty = true;
		}
		// 指定されたリストの子供を表示する
		elseif( 'in_list' == $wp_query->get('post_type') ){
			$wp_query->set('post_type', 'any');
			$this->list_id = (int) $wp_query->get('post_parent');
			$wp_query->set('post_parent', '');
		}
		// リスト一覧なら、publishのみ
		elseif( 'lists' == $wp_query->get('post_type') ){
			if( $wp_query->get('p') ){
				if( !current_user_can('edit_post', $wp_query->get('p')) ){
					$this->exclude_empty = true;
				}
			}else{
				if( !$wp_query->get('post_status') ){
					$wp_query->set('post_status', 'publish');
				}
				$this->exclude_empty = true;
			}
		}
	}


	/**
	 * リストIDに登録する
	 *
	 * @param string $join
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed|string
	 */
	public function posts_join( $join, \WP_Query $wp_query ) {
		if( $this->list_id ){
			$join .= <<<SQL
			LEFT JOIN {$this->lists->table}
			ON {$this->lists->posts}.ID = {$this->lists->table}.object_id
SQL;
		}elseif( $this->exclude_empty ){
			$join .= <<<SQL
			LEFT JOIN (
				SELECT list_table.subject_id, COUNT(list_table.object_id) AS count
				FROM {$this->lists->table} AS list_table
				LEFT JOIN {$this->lists->posts} AS list_child
				ON list_table.object_id = list_child.ID
				WHERE list_child.post_status = 'publish'
				GROUP BY list_table.subject_id
			) AS list_children
			ON list_children.subject_id = {$this->lists->posts}.ID
SQL;
		}
		return $join;
	}

	/**
	 * リストIDで絞り込む
	 *
	 * @param string $where
	 * @param \WP_Query $wp_query
	 *
	 * @return string
	 */
	public function posts_where( $where, \WP_Query $wp_query ) {
		if( $this->list_id ){
			$query = <<<SQL
			AND (
				{$this->lists->table}.rel_type = %s
				AND
				{$this->lists->table}.subject_id = %d
			)
SQL;
			$where .= $this->db->prepare($query, 'list', $this->list_id);
		}elseif( $this->exclude_empty ){
			$where .= <<<SQL
			AND ( list_children.count > 0 )
SQL;
		}

		return $where;
	}


	/**
	 * リストIDをリセットする
	 *
	 * @param string $request
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed|string
	 */
	public function posts_request( $request, \WP_Query $wp_query ) {
		$this->list_id = 0;
		$this->exclude_empty = false;
		return $request;
	}


	/**
	 * Posts results
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 * @return array
	 */
	public function the_posts(array $posts, \WP_Query $wp_query){
		$lists = [];
		// リストの数を調べる
		foreach( $posts as $post ){
			if( 'lists' === $post->post_type ){
				$lists[$post->ID] = 0;
			}
		}

		// あればクエリ発行
		if( count($lists) ){
			foreach( $this->lists->num_children(array_keys($lists)) as $row){
				if( isset($lists[$row->subject_id]) ){
					$lists[$row->subject_id] = $row->count;
				}
			}
		}
		// 投稿データにアサイン
		foreach( $posts as &$post ){
			if( isset($lists[$post->ID]) ){
				$post->num_children = $lists[$post->ID];
			}
		}
		return $posts;
	}

} 