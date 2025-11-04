<?php

namespace Hametuha\Model;


/**
 * Review class
 *
 * @feature-group review-tag
 * @package Hametuha\Model
 */
class Review extends TermUserRelationships {


	/**
	 * レビュー用タクソノミー
	 *
	 * @var string
	 */
	public $taxonomy = 'review';


	/**
	 * フィードバック用のタグ名
	 *
	 * @var array
	 */
	public $feedback_tags = array(
		'intelligence' => array( '知的', 'バカ' ),
		'completeness' => array( 'よくできてる', '破滅してる' ),
		'readability'  => array( 'わかりやすい', '前衛的' ),
		'emotion'      => array( '泣ける', '笑える' ),
		'mood'         => array( '生きたくなる', '死にたくなる' ),
		'to_author'    => array( '作者を褒めたい', '作者を殴りたい' ),
	);


	/**
	 * レビュー用タグのキー名からラベルを返す
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function review_tag_label( $key ) {
		switch ( $key ) {
			case 'intelligence':
				$label = '作品の知性';
				break;
			case 'completeness':
				$label = '作品の完成度';
				break;
			case 'readability':
				$label = '作品の構成';
				break;
			case 'emotion':
				$label = '作品から得た感情';
				break;
			case 'mood':
				$label = '作品を読んで';
				break;
			case 'to_author':
				$label = '作者の印象';
				break;
			default:
				$label = '';
				break;
		}

		return $label;
	}


	/**
	 * ユーザーが指定したレビュータグを指定した投稿につけているか
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param string $tag_name
	 *
	 * @return boolean
	 */
	public function is_user_vote_for( $user_id, $post_id, $tag_name ) {
		return (bool) $this->select( "{$this->table}.object_id" )
						->wheres( [
							"{$this->table}.user_id = %d" => $user_id,
							"{$this->table}.object_id = %d" => $post_id,
							"{$this->taxonomy}.taxonomy = %s" => $this->taxonomy,
							"{$this->terms}.name = %s"    => $tag_name,
						] )->get_var();
	}

	/**
	 * レビュー数を取得する
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function get_review_count( $post_id ) {
		return (int) get_post_meta( $post_id, '_review_count', true );
	}

	/**
	 * レビュー数を更新する
	 *
	 * @param int $post_id
	 * @param int $increment
	 *
	 * @return int
	 */
	public function update_review_count( $post_id, $increment = 1 ) {
		$count  = $this->get_review_count( $post_id );
		$count += $increment;
		update_post_meta( $post_id, '_review_count', $count );

		return $count;
	}

	/**
	 * 投稿に付けられたレビュータグの集計データをpost_metaとしてキャッシュ
	 *
	 * @param int $post_id
	 * @return bool 値が更新された場合はtrue、変更がなかった場合はfalse
	 */
	public function update_post_review_tags( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'post' !== $post->post_type ) {
			return false;
		}

		$updated = false;

		// 各タグの獲得数を取得
		$tag_counts = $this->get_post_chart_points( $post->ID, false );

		// すべてのタグをリセット（既存のメタを削除）
		foreach ( $this->feedback_tags as $key => $terms ) {
			foreach ( $terms as $tag_name ) {
				delete_post_meta( $post->ID, '_review_tag_' . $tag_name );
			}
		}

		// 新しい集計データを保存
		foreach ( $tag_counts as $tag_data ) {
			$meta_key = '_review_tag_' . $tag_data->name;
			$count    = (int) $tag_data->score;
			if ( update_post_meta( $post->ID, $meta_key, $count ) ) {
				$updated = true;
			}
		}

		return $updated;
	}

	/**
	 * ユーザーがつけたレビュータグのリストを返す
	 *
	 * @param int $user_id
	 * @param int $post_id
	 *
	 * @return array タームリスト
	 */
	public function user_voted_tags( $user_id, $post_id ) {
		if ( ! $user_id ) {
			return [];
		}

		return (array) $this->select( "{$this->terms}.*, {$this->term_taxonomy}.*" )
							->wheres( [
								"{$this->table}.user_id = %d"          => $user_id,
								"{$this->table}.object_id = %d"        => $post_id,
								"{$this->term_taxonomy}.taxonomy = %s" => $this->taxonomy,
							] )->result();
	}

	/**
	 * ユーザーがつけたレビューをすべて消す
	 *
	 * @param int $user_id
	 * @param int $post_id
	 *
	 * @return false|int
	 */
	public function clear_user_review( $user_id, $post_id ) {
		$result = $this->delete_where( [
			[ 'user_id', '=', $user_id, '%d' ],
			[ 'object_id', '=', $post_id, '%d' ],
		] );

		return $result;
	}

	/**
	 * レビューを保存する
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 *
	 * @return false|int
	 */
	public function add_review( $user_id, $post_id, $term_taxonomy_id ) {
		return $this->insert( [
			'user_id'          => $user_id,
			'object_id'        => $post_id,
			'term_taxonomy_id' => $term_taxonomy_id,
		] );
	}

	/**
	 * JOINを繋げる
	 *
	 * @param string $join
	 *
	 * @return string
	 */
	public function reviewed_join( $join ) {
		$join .= <<<SQL
          INNER JOIN {$this->table}
          ON {$this->table}.object_id = {$this->posts}.ID
          INNER JOIN {$this->term_taxonomy} AS review
          ON review.term_taxonomy_id = {$this->table}.term_taxonomy_id
SQL;

		return $join;
	}

	/**
	 * レビューのWHEREを返す
	 *
	 * @param string $where
	 * @param int $user_id
	 *
	 * @return string
	 */
	public function reviewed_where( $where, $user_id ) {
		$new_where = <<<SQL
          AND (
            {$this->table}.user_id = %d
            AND
            review.taxonomy = %s
          )
SQL;

		return $where . $this->db->prepare( $new_where, $user_id, $this->taxonomy );
	}

	/**
	 * Get chart's JSON
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function get_chart( \WP_Post $post ) {
		$data = [
			'labels'   => [ '知性', '完成度', '構成', '読後感', '好感度', '作者' ],
			'datasets' => [
				[
					'label'                     => '健全指数',
					'backgroundColor'           => 'rgba(172, 255, 165, 0.4)',
					'borderColor'               => 'rgba(172, 255, 165, 0.8)',
					'pointBackgroundColor'      => 'rgba(172, 255, 165, 1)',
					'pointBorderColor'          => '#fff',
					'pointHoverBackgroundColor' => '#fff',
					'pointHoverBorderColor'     => 'rgba(172, 255, 165, 1)',
					'data'                      => [],
					'label_set'                 => [],
				],
				[
					'label'                     => '破滅指数',
					'backgroundColor'           => 'rgba(232, 76, 63, 0.4)',
					'borderColor'               => 'rgba(232, 76, 63, 0.8)',
					'pointBackgroundColor'      => 'rgba(232, 76, 63, 1)',
					'pointBorderColor'          => '#fff',
					'pointHoverBackgroundColor' => '#fff',
					'pointHoverBorderColor'     => 'rgba(232, 76, 63, 1)',
					'data'                      => [],
					'label_set'                 => [],
				],
			],
		];
		// ポイントを取得
		$points = $this->get_post_chart_points( $post->ID, ( 'series' == $post->post_type ) );

		// データ整形
		$labels = [
			[],
			[],
		];
		foreach ( $this->feedback_tags as $key => $val ) {
			list( $pos, $nega ) = $val;
			$labels[0][]        = $pos;
			$labels[1][]        = $nega;
			for ( $i = 0, $l = count( $val ); $i < $l; $i ++ ) {
				$score = 0;
				foreach ( $points as $point ) {
					if ( $point->name == $val[ $i ] ) {
						$score = $point->score;
						break;
					}
				}
				$score = min( $score * 20, 100 );
				// TODO: 投稿数が少な過ぎてたぶん意味ないので、平均は取らない
				//                $avg = get_review_average($val[$i]);
				//                $points[$i][] = ($point > $avg * 2) ? 100 : round($point / $avg * 50) ;
				$data['datasets'][ $i ]['data'][]      = $score;
				$data['datasets'][ $i ]['label_set'][] = $val[ $i ];
			}
		}
		$json = json_encode( [
			'data'   => $data,
			'labels' => $labels,
		] );
		$html = <<<HTML
<div>
<canvas id="single-radar" width="300" height="300"></canvas>
<script type="text/javascript">
window.postScore = {$json};
</script>
</div>
HTML;

		return $html;
	}

	/**
	 * チャートの点数を取得する
	 *
	 * @param int $post_id
	 * @param bool $parent
	 *
	 * @return array
	 */
	public function get_post_chart_points( $post_id, $parent = false ) {
		$this->select( "COUNT({$this->table}.user_id) AS score, {$this->terms}.name" )
			 ->where( "{$this->term_taxonomy}.taxonomy = %s", $this->taxonomy )
			 ->group_by( "{$this->terms}.term_id" );
		if ( $parent ) {
			$sub_query = <<<SQL
                SELECT ID FROM {$this->db->posts}
                WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d
SQL;
			$this->where_in_subquery( "{$this->table}.object_id", $this->db->prepare( $sub_query, $post_id ) );
		} else {
			$this->where( "{$this->table}.object_id = %d", $post_id );
		}

		return $this->result();
	}

	/**
	 * 投稿者がいままでに獲得したレビューを取得する
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public function get_author_chart_points( $user_id ) {
		foreach ( $this->default_join() as $join ) {
			call_user_func_array( [ $this, 'join' ], $join );
		}
		$result = $this->select( "COUNT({$this->table}.user_id) AS score, {$this->terms}.name" )
					   ->join( $this->posts, "{$this->table}.object_id = {$this->posts}.ID" )
					->wheres( [
						"{$this->term_taxonomy}.taxonomy = %s" => $this->taxonomy,
						"{$this->posts}.post_author = %d" => $user_id,
					] )
					   ->group_by( "{$this->terms}.term_id" )
					   ->result();
		$json   = [];
		foreach ( $this->feedback_tags as $key => $terms ) {
			$json[ $key ] = [
				'genre'    => $this->review_tag_label( $key ),
				'positive' => [],
				'negative' => [],
			];
			for ( $i = 0; $i < 2; $i ++ ) {
				$value = 0;
				$label = $i ? 'negative' : 'positive';
				foreach ( $result as $r ) {
					if ( $r->name === $terms[ $i ] ) {
						$value = (int) $r->score;
						break;
					}
				}
				$json[ $key ][ $label ] = [
					'label' => $terms[ $i ],
					'value' => (int) $value,
				];
			}
		}

		return $json;
	}

	/**
	 * 投稿に付けられたタグを取得する
	 *
	 * @param int   $user_id
	 * @param array $args
	 * @return \WP_Comment[]
	 */
	public function get_author_comments( $user_id, $args = [] ) {
		$args = wp_parse_args( $args, [
			'paged'          => 1,
			'posts_per_page' => 20,
			's'              => '',
		] );
		$paged          = $args['paged'];
		$posts_per_page = $args['posts_per_page'];
		$offset         = ( max( 1, $paged ) - 1 ) * $posts_per_page;
		$wheres = [
			$this->db->prepare( 'p.post_author = %d', $user_id ),
			'p.post_type = "post"',
			$this->db->prepare( 'c.user_id != %d', $user_id ),
		];
		if ( ! empty( $args['s'] ) ) {
			$wheres[] = $this->db->prepare( 'c.comment_content LIKE %s', '*' . $args['s'] . '*' );
		}
		$wheres = implode( ' AND ', $wheres );
		$sql = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS
		    	c.*
			FROM {$this->db->comments} as c
			LEFT JOIN {$this->db->posts} as p
			ON p.ID = c.comment_post_ID
			WHERE {$wheres}
			ORDER BY c.comment_date DESC
			LIMIT %d, %d
SQL;
		$result = $this->db->get_results( $this->db->prepare( $sql, $offset, $posts_per_page ) );
		$found  = (int) $this->db->get_var( 'SELECT FOUND_ROWS()' );
		return [
			'found'    => $found,
			'current'  => $paged,
			'total'    => ceil( $found / $posts_per_page ),
			'comments' => array_map( function( $row ) {
				return new \WP_Comment( $row );
			}, $result ),
		];
	}
}

