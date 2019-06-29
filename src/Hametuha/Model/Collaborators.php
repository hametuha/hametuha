<?php

namespace Hametuha\Model;



use Hametuha\Notifications\Emails\CollaboratorDelete;
use Hametuha\Pattern\Singleton;

/**
 * Collaborators
 * @package hametuha
 * @property \wpdb  $db
 * @property string $relationships
 */
class Collaborators extends Singleton {

	public $owner_types = [
		'producer'     => '監修',
		'editor'       => '編集',
		'writer'       => '著',
		'self_produce' => '編著',
	];

	public $collaborator_type = [
		'writer'      => '著',
		'editor'      => '編集',
		'designer'    => 'デザイン',
		'illustrator' => 'イラスト',
		'translator'  => '翻訳',
	];

	public $share_type = [
		'equality'   => '均等割',
		'proportion' => '変動割合',
	];

	protected $rel_type = 'collabo';

	/**
	 * Executed inside constructor.
	 */
	protected function init() {

	}

	/**
	 * Get series owner type.
	 *
	 * @param int $series_id
	 * @return string
	 */
	public function owner_type( $series_id ) {
		return get_post_meta( $series_id, '_owner_type', true ) ?: 'self_produce';
	}

	/**
	 * Get current share type
	 *
	 * @param int $series_id
	 * @return string
	 */
	public function current_share_type( $series_id ) {
		return get_post_meta( $series_id, '_share_type', true ) ?: 'equality';
	}

	/**
	 * Get owner type label.
	 *
	 * @param int $series_id
	 * @return string
	 */
	public function owner_label( $series_id ) {
		return $this->owner_types[ $this->owner_type( $series_id ) ];
	}

	/**
	 * Get single collaborator.
	 *
	 * @param int $series_id
	 * @param int $user_id
	 * @return \WP_User|null
	 */
	public function collaborator( $series_id, $user_id ) {
		$collaborators = $this->get_collaborators( $series_id, $user_id );
		return $collaborators ? $collaborators[0] : null;
	}

	/**
	 * Detect if specified collaborator exists.
	 *
	 * @param int  $series_id
	 * @param int  $user_id
	 * @param bool $only_valid If true, only confirmed user is returned.
	 * @return bool
	 */
	public function collaborator_exists( $series_id, $user_id, $only_valid = false ) {
		$query = <<<SQL
			SELECT ID FROM {$this->relationships}
			WHERE rel_type  = %d
			  AND object_id = %d
			  AND user_id   = %d
SQL;
		$wheres = [ $this->rel_type, $series_id, $user_id ];
		if ( $only_valid ) {
			$query .= ' AND location >= 0';
		}
		$query .= ' LIMIT 1';
		array_unshift( $wheres, $query );
		return (bool) $this->db->get_var( call_user_func_array( [ $this->db, 'prepare' ], $wheres ) );
	}

	/**
	 * Add collaborator
	 *
	 * @param int    $series_id
	 * @param int    $user_id
	 * @param string $type
	 * @param float  $location
	 * @return \WP_User|\WP_Error
	 */
	public function add_collaborator( $series_id, $user_id, $type = 'writer', $location = -0.1 ) {
		$post = $this->validate_series( $series_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		if ( $this->collaborator( $post->ID, $user_id ) ) {
			return new \WP_Error( 'existing_collaborator', 'そのユーザーはすでに登録されています。', [
				'status' => 400,
			] );
		}
		$result = $this->db->insert( $this->relationships, [
			'rel_type'  => $this->rel_type,
			'object_id' => $post->ID,
			'user_id'   => $user_id,
			'location'  => $location,
			'content'   => $type,
			'updated'   => current_time( 'mysql' ),
		], [ '%s', '%d', '%d', '%f', '%s', '%s' ] );
		if ( ! $result ) {
			return new \WP_Error( 'failed_update', 'ユーザーを追加できませんでした。', [
				'status' => 500,
				'error'  => $this->db->last_error,
				'query'  => $this->db->last_query,
			] );
		}
		$actually_added = $this->collaborator( $post->ID, $user_id );
		if ( ! $actually_added ) {
			return new \WP_Error( 'invalid_collaborator', '追加した協力者を取得できませんでした。', [
				'status' => 404,
			] );
		}
		// TODO: We should notify user to confirmation action.
		return $actually_added;
	}

	/**
	 * Update confirmation.
	 *
	 * @param int $series_id
	 * @param int $user_id
	 * @return bool
	 */
	public function confirm_invitation( $series_id, $user_id ) {
		// TODO: The revenue should be fixed before invitation.
		return (bool) $this->db->update( $this->relationships, [
			'location' => 0,
		], [
			'rel_type'  => $this->rel_type,
			'object_id' => $series_id,
			'user_id'   => $user_id,
		], [ '%d' ], [ '%s', '%d', '%d' ] );
	}

	/**
	 * Get collaborators list.
	 *
	 * @param int $series_id
	 * @param int $user_id
	 * @param int $paged
	 * @param int $per_page
	 * @return \WP_User[]
	 */
	private function get_list( $series_id = 0, $user_id = 0, $paged = 1, $per_page = 0 ) {
		$query = <<<SQL
			SELECT
			       u.*,
			       r.object_id as post_id, r.location AS ratio, r.updated AS assigned, r.content AS `collaboration_type`,
			       r.updated
			FROM {$this->relationships} AS r
			INNER JOIN {$this->db->users} AS u
			ON r.user_id = u.ID
			WHERE rel_type  = %s
SQL;
		$wheres = [ $this->rel_type ];
		if ( $series_id ) {
			$query .= ' AND object_id = %d ';
			$wheres[] = $series_id;
		}
		if ( $user_id ) {
			$query .= ' AND user_id = %d';
			$wheres[] = $user_id;
		}
		if ( $per_page ) {
			$query .= ' LIMIT %d, %d';
			$wheres[] = ( max( 1, $paged ) -1 ) * $per_page;
			$wheres[] = $per_page;
		}
		array_unshift( $wheres, $query );
		return array_map( function( \stdClass $collaborator ) {
			return new \WP_User( $collaborator );
		}, $this->db->get_results( call_user_func_array(  [$this->db, 'prepare' ], $wheres ) ) );
	}

	/**
	 * Get all collaborators
	 *
	 * @param int $series_id
	 * @param int $user_id Default 0.
	 * @return \WP_User[]
	 */
	public function get_collaborators( $series_id, $user_id = 0 ) {
		return $this->get_list( $series_id, $user_id );
	}

	/**
	 * Get all invitations for user.
	 *
	 * @param int $user_id
	 * @param int $paged
	 * @param int $per_page Default is 20.
	 * @return \WP_User[]
	 */
	public function get_invitations( $user_id, $paged = 1, $per_page = 20 ) {
		return $this->get_list( 0, $user_id, $paged, $per_page );
	}

	/**
	 * Get total invitation count.
	 *
	 * @param int $user_id
	 * @return int
	 */
	public function total_invitations( $user_id ) {
		global $wpdb;
		$query = <<<SQL
			SELECT COUNT( ID ) FROM {$this->relationships}
			WHERE rel_type = %s
			  AND user_id  = %d
SQL;
		return (int) $wpdb->get_var( $wpdb->prepare( $query, $this->rel_type, $user_id ) );
	}

	/**
	 * Update user's margin.
	 *
	 * @param int $series_id
	 * @param int $user_id
	 * @param int $margin
	 * @return bool|\WP_Error
	 */
	public function update_margin( $series_id, $user_id, $margin ) {
		$existing_margins = array_sum( array_values( $this->get_margin_list( $series_id, [ $user_id ] ) ) );
		if ( 100 < $margin + $existing_margins ) {
			return new \WP_Error( 'too_much_revenue', '報酬の合計が100%を超えています。', [
				'status' => 400,
			] );
		}
		global $wpdb;
		$result = $wpdb->update( $this->relationships, [
			'location' => $margin / 100,
		], [
			'rel_type'  => $this->rel_type,
			'object_id' => $series_id,
			'user_id'   => $user_id,
		], [ '%f' ], [ '%s', '%d', '%d' ] );
		return $result ?: new \WP_Error( 'failed_update', '報酬を更新できませんでした。', [
			'status' => 500,
		] );
	}

	/**
	 * Get series ID
	 *
	 * @param int   $series_id
	 * @param int[] $excludes  Array of user IDs to exclude.
	 * @return array
	 */
	public function get_margin_list( $series_id, $excludes = [] ) {
		global $wpdb;
		$query = <<<SQL
			SELECT * FROM {$this->relationships}
			WHERE rel_type  = %s
			  AND object_id = %d
			  AND location >= 0
SQL;
		if ( $excludes ) {
			$excludes = (array) $excludes;
			$query .= sprintf( ' AND user_id NOT IN (%s)', implode( ', ', array_map( 'intval', $excludes ) ) );
		}
		$margins = [];
		foreach ( $wpdb->get_results( $wpdb->prepare( $query, $this->rel_type, $series_id ) ) as $row ) {
			$margins[ $row->user_id ] = (int) $row->location * 100;
		}
		return $margins;
	}

	/**
	 * Delete collaborator.
	 *
	 * @param int $series_id
	 * @param int $user_id
	 * @return bool|\WP_Error
	 */
	public function delete_collaborator( $series_id, $user_id ) {
		$post = $this->validate_series( $series_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		if ( $post->post_author == $user_id ) {
			return new \WP_Error( 'invalid_collaborator_to_delete', '作品集の所有者は削除できません。', [
				'status' => 404,
			] );
		}
		$query = <<<SQL
			DELETE FROM {$this->relationships}
			WHERE rel_type  = %s
			  AND object_id = %d
			  AND user_id   = %d
SQL;
		if ( ! $this->db->query( $this->db->prepare( $query, $this->rel_type, $post->ID, $user_id ) ) ) {
			return new \WP_Error( 'no_collaborator_deleted', '指定されたユーザーは存在しません。', [
				'status' => 404,
			] );
		}
		CollaboratorDelete::exec( [
			$user_id => [
				'url'   => get_permalink( $series_id ),
				'title' => get_the_title( $series_id ),
			],
		] );
		return true;
	}

	/**
	 * Validate post.
	 *
	 * @param int $series_id
	 * @return \WP_Post|\WP_Error
	 */
	private function validate_series( $series_id ) {
		$post = get_post( $series_id );
		if ( ! $post || 'series' !== $post->post_type ) {
			return new \WP_Error( 'series_not_found', '作品集が見つかりません。', [
				'status' => 404,
			] );
		} else {
			return $post;
		}
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'db':
				global $wpdb;
				return $wpdb;
			case 'relationships':
				return $this->db->prefix . 'user_content_relationships';
			default:
				return null;
		}
	}
}

