<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Class Anpis
 * @package Hametuha\Model
 * @property-read string $user_content_relationships
 */
class Anpis extends Model {

	protected $related = [ 'user_content_relationships' ];

	/**
	 * Create tweet
	 *
	 * @param int    $user_id
	 * @param string $content
	 * @param array  $mention
	 *
	 * @return int|\WP_Error
	 */
	public function create_tweet( $user_id, $content, $mention = [] ) {
		$id      = $this->get_pseudo_name();
		$post_id = wp_insert_post( [
			'post_type'    => 'anpi',
			'post_name'    => $id,
			'post_title'   => "つぶやき{$id}",
			'post_content' => '',
			'post_excerpt' => $content,
			'post_status'  => 'publish',
		], true );
		if ( ! is_wp_error( $post_id ) ) {
			wp_cache_delete( 'biggest_id', 'anpi' );
			update_post_meta( $post_id, '_is_tweet', true );
			if ( $mention ) {
				foreach ( $mention as $m ) {
					$this->insert( [
						'rel_type'  => 'mention',
						'object_id' => $post_id,
						'user_id'   => $m,
						'location'  => 1,
						'updated'   => current_time( 'mysql' ),
					], [ '%s', '%d', '%d', '%f', '%s' ], $this->user_content_relationships );
				}
			}
		}
		return $post_id;
	}

	/**
	 * Create base anpi and returns it.
	 *
	 * @param int $user_id
	 *
	 * @return int|\WP_Error
	 */
	public function create_base_anpi( $user_id ) {
		$id = $this->get_pseudo_name();
		return wp_insert_post( [
			'post_type'    => 'anpi',
			'post_name'    => $id,
			'post_title'   => 'ここにタイトルを入れてください',
			'post_content' => '',
			'post_author'  => $user_id,
			'post_status'  => 'auto-draft',
		], true );
	}

	/**
	 * Get mentioned users
	 *
	 * @param array $post_ids
	 *
	 * @return array Array of WP_User
	 */
	public function get_mentioned( $post_ids ) {
		if ( ! $post_ids ) {
			return [];
		}
		$users  = $this->select( 'r.object_id, u.*' )
					   ->from( "{$this->user_content_relationships} AS r" )
					   ->join( "{$this->db->users} AS u", 'u.ID = r.user_id', 'inner' )
					   ->where( 'r.rel_type = %s', 'mention' )
					   ->where_in( 'r.object_id', $post_ids, '%d' )
					   ->result();
		$result = [];
		foreach ( $users as $user ) {
			if ( ! isset( $result[ $user->object_id ] ) ) {
				$result[ $user->object_id ] = [];
			}
			$result[ $user->object_id ][] = new \WP_User( $user );
		}

		return $result;
	}

	/**
	 * Get pseudo post name for anpi
	 *
	 * @return int
	 */
	public function get_pseudo_name() {
		return $this->get_biggest_id() + 1;
	}

	/**
	 * Get biggest ID
	 *
	 * @return int
	 */
	protected function get_biggest_id() {
		$cache = wp_cache_get( 'biggest_id', 'anpi' );
		if ( false == $cache ) {
			$cache = (int) $this->select( 'ID' )
								->from( $this->db->posts )
								->order_by( 'ID', 'DESC' )
								->limit( 1 )->get_var();
			if ( $cache ) {
				wp_cache_set( 'biggest_id', $cache, 'anpi', 0 );
			}
		}

		return (int) $cache;
	}

	/**
	 * Detect if post is tweet.
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return bool
	 */
	public function is_tweet( $post = null ) {
		$post = get_post( $post );
		return 'anpi' == $post->post_type && get_post_meta( $post->ID, '_is_tweet', true );
	}

}
