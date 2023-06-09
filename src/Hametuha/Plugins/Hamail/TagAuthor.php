<?php

namespace Hametuha\Plugins\Hamail;


use Hametuha\Hamail\Model\SearchResultItem;
use Hametuha\Hamail\Pattern\RecipientSelector;

/**
 * Select recipients from tag author.
 *
 * @package hametuha
 */
class TagAuthor extends RecipientSelector {

	protected $namespace = 'hametuha/v1';

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
		$term_ids    = array_map( function( $id ) {
			$taxonomy = explode( '_', $id );
			$term_id  = array_pop( $taxonomy );
			return intval( $term_id );
		}, $ids );
		if ( empty( $term_ids ) ) {
			return [];
		}
		$term_ids = implode( ',', $term_ids );
		global $wpdb;
		$query    = <<<SQL
			SELECT p.post_author
			FROM {$wpdb->term_relationships} AS tr
			LEFT JOIN {$wpdb->posts} AS p
			ON tr.object_id = p.ID
			WHERE tr.term_taxonomy_id IN ( {$term_ids} )
			GROUP BY p.post_author
SQL;
		$user_ids = $wpdb->get_col( $query );
		if ( empty( $user_ids ) ) {
			return [];
		}
		$result      = $this->user_to_item( [
			'include' => array_map( 'intval', $user_ids ),
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
				t.*, tt.*
			FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON t.term_id = tt.term_taxonomy_id
			WHERE ( t.name LIKE %s OR tt.description LIKE %s )
			ORDER BY tt.count DESC
			LIMIT %d, %d
SQL;
		$term        = "%{$term}%";
		$query       = $wpdb->prepare( $query, $term, $term, ( $paged - 1 ) * $this->per_page, $this->per_page );
		$results     = $wpdb->get_results( $query );
		$this->total = parent::get_search_total( $term, $paged );
		return array_map( function( $result ) {
			$taxonomy_obj = get_taxonomy( $result->taxonomy );
			$id           = sprintf( '%s_%d', $result->taxonomy, $result->term_id );
			$type         = 'term';
			$label        = sprintf( 'Authors of posts in %1$s: %2$s(%3$d)', $taxonomy_obj->label, $result->name, $result->count );
			return new SearchResultItem( $id, $label, 'term' );
		}, $results );
	}

	protected function get_search_total( $term, $paged ) {
		return $this->total;
	}
}
