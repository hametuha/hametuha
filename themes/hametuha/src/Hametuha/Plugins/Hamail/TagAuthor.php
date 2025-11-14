<?php

namespace Hametuha\Plugins\Hamail;


use Hametuha\Hamail\Model\SearchResultItem;
use Hametuha\Hamail\Pattern\RecipientSelector;
use Hametuha\Hooks\CampaignController;

/**
 * Select recipients from tag author.
 *
 * @package hametuha
 */
class TagAuthor extends RecipientSelector {

	protected $namespace = 'hametuha/v1';

	/**
	 * @var int 現在の総数
	 */
	protected $total = 0;

	protected function route() {
		return 'recipients/tag-authors';
	}


	/**
	 * Search ids to user object.
	 *
	 * @param string[] $ids
	 *
	 * @return SearchResultItem[]|void|\WP_Error
	 */
	protected function get_from_ids( $ids ) {
		$this->total = 0;
		$term_ids    = array_map( function ( $id ) {
			$taxonomy = explode( '_', $id );
			$term_id  = array_pop( $taxonomy );
			return intval( $term_id );
		}, $ids );
		if ( empty( $term_ids ) ) {
			return [];
		}
		$in_clause = implode( ',', $term_ids );
		// 投稿の作者
		global $wpdb;
		$query    = <<<SQL
			SELECT p.post_author
			FROM {$wpdb->term_relationships} AS tr
			LEFT JOIN {$wpdb->posts} AS p
			ON tr.object_id = p.ID
			WHERE tr.term_taxonomy_id IN ( {$in_clause} )
			GROUP BY p.post_author
SQL;
		$user_ids = array_map( 'intval', $wpdb->get_col( $query ) );
		// サポーター
		foreach ( CampaignController::get_instance()->get_supporters( $term_ids ) as $user ) {
			if ( ! in_array( $user->ID, $user_ids, true ) ) {
				$user_ids[] = $user->ID;
			}
		}
		// todo: コメントとか、イベント参加者とか、他の条件を追加する？
		if ( empty( $user_ids ) ) {
			return [];
		}
		$result      = $this->user_to_item( [
			'include' => $user_ids,
			'orderby' => 'user_registered',
			'order'   => 'ASC',
		] );
		$this->total = count( $result );
		return $result;
	}

	/**
	 * Search tags matches query.
	 *
	 * @param string $term
	 * @param int    $paged
	 *
	 * @return SearchResultItem[]|void|\WP_Error
	 */
	protected function search( $term, $paged = 1 ) {
		global $wpdb;
		$query       = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS
				t.*, tt.taxonomy, ttr.post_count
			FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON t.term_id = tt.term_taxonomy_id
			LEFT JOIN (
				SELECT term_taxonomy_id, COUNT( DISTINCT object_id ) AS post_count
				FROM {$wpdb->term_relationships}
				GROUP BY term_taxonomy_id
			) AS ttr
			ON ttr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE ( t.name LIKE %s OR tt.description LIKE %s )
			ORDER BY tt.count DESC
			LIMIT %d, %d
SQL;
		$term        = "%{$term}%";
		$query       = $wpdb->prepare( $query, $term, $term, ( $paged - 1 ) * $this->per_page, $this->per_page );
		$results     = $wpdb->get_results( $query );
		$this->total = parent::get_search_total( $term, $paged );
		return array_map( function ( $result ) {
			$taxonomy_obj = get_taxonomy( $result->taxonomy );
			$id           = sprintf( '%s_%d', $result->taxonomy, $result->term_id );
			$type         = 'term';
			$label        = sprintf( __( '%1$s「%2$s」のついた投稿（%3$d件）の関係者', 'hametuha' ), $taxonomy_obj->label, $result->name, $result->post_count );
			return new SearchResultItem( $id, $label, 'term' );
		}, $results );
	}

	/**
	 * 現在の総数を返す
	 *
	 * @param string $term
	 * @param int    $paged
	 * @return int
	 */
	protected function get_search_total( $term, $paged ) {
		return $this->total;
	}
}
