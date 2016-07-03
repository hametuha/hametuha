<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 *
 *
 * @package Hametuha\Model
 * @property-read string $posts
 * @property-read string $usermeta
 * @property-read string $postmeta
 * @property-read string $user_content_relationships
 */
class Author extends Model {

	protected $name = 'users';

	protected $related = [
		'posts',
		'usermeta',
		'postmeta',
		'user_content_relationships',
	];

	public function author_list_query( $offset, $per_page ) {

	}

	/**
	 * Get user by nice name
	 *
	 * @param string $nice_name
	 *
	 * @return false|\WP_User
	 */
	public function get_by_nice_name( $nice_name ) {
		$user_id = (int) $this->select( 'ID' )
							  ->where( 'user_nicename = %s', $nice_name )
							  ->get_var();

		return get_userdata( $user_id );
	}

	/**
	 * 登録日数を返す
	 *
	 * @param int $user_id
	 *
	 * @return int|mixed
	 */
	public function get_active_days( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return 0;
		}
		$tz         = new \DateTimeZone( 'Asia/Tokyo' );
		$registered = new \DateTime( $user->user_registered, $tz );
		$now        = new \DateTime( 'now', $tz );

		return $now->diff( $registered, true )->days;
	}

	/**
	 * 投稿者の総文字数を取得する
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_letter_count( $user_id ) {
		return (int) $this->select( 'SUM( CHAR_LENGTH(post_content) )' )
						  ->from( $this->posts )
						  ->where_in( 'post_type', get_post_types( [ 'public' => true ] ) )
						  ->wheres( [
							  'post_status = %s' => 'publish',
							  'post_author = %d' => $user_id,
						  ] )
						  ->get_var();
	}

	/**
	 * 投稿者の獲得したスターの返す
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_star_count( $user_id ) {
		return (int) $this->select( 'SUM(u.location * 10)' )
						  ->from( "{$this->user_content_relationships} as u" )
						  ->join( "{$this->posts} AS p", 'u.object_id = p.ID' )
						  ->wheres( [
							  'u.rel_type = %s'    => 'rank',
							  'p.post_author = %d' => $user_id,
						  ] )->get_var();
	}

	/**
	 * 最近のユーザーの情報を取得する
	 *
	 * @param $user_id
	 *
	 * @return array|null|object
	 */
	public function get_activities( $user_id ){
		$query = <<<SQL
			(
				SELECT
				   ID as post_id,
				   post_parent AS parent_id,
				   post_type AS type,
				   post_date AS date
				FROM {$this->posts}
				WHERE post_author = %d
				  AND post_type IN ('anpi', 'post', 'series', 'announcement', 'newsletter', 'faq')
				  AND post_status = 'publish'
				ORDER BY post_date DESC
				LIMIT 10
			)
			UNION ALL
			(
				SELECT
					comment_ID AS post_id,
					comment_post_ID AS parent_id,
					'comment' AS post_type,
					comment_date AS date
				FROM {$this->db->comments}
				WHERE user_id = %d
				  AND comment_approved = '1'
			    ORDER BY comment_date DESC
			    LIMIT 10
			)
			UNION ALL
			(
				SELECT
				   p.ID AS post_id,
				   p.post_parent AS parent_id,
				   'review' AS post_type,
				   u.updated AS date
				FROM {$this->user_content_relationships} AS u
				LEFT JOIN {$this->posts} AS p
				ON u.object_id = p.ID
				WHERE u.rel_type = 'rank'
				  AND u.user_id = %d
				  AND u.location >= 0.3
				ORDER BY u.updated DESC
				LIMIT 10
			)
			ORDER BY date DESC
			LIMIT 10
SQL;
		return $this->db->get_results($this->db->prepare($query, $user_id, $user_id, $user_id));

	}

	/**
	 * 取得したSNSの数を出力する
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_sns_count( $user_id ) {
		return (int) $this->select( 'SUM(CAST(pm.meta_value AS SIGNED))' )
						  ->from( "{$this->postmeta} AS pm" )
						  ->join( "{$this->posts} AS p", 'p.ID = pm.post_id' )
						  ->where( 'p.post_author = %d', $user_id )
						  ->where_in( 'pm.meta_key', array_map( function ( $b ) {
							  return '_sns_count_' . $b;
						  }, [ 'facebook', 'twitter', 'hatena', 'googleplus', 'googleplus' ] ) )
						  ->get_var();
	}

	/**
	 * ニュースの投稿者を取得する
	 *
	 * @return array
	 */
	public function get_journalists() {
		$users = $this->select( ', u.*' )
		            ->distinct( 'u.ID' )
		            ->from( "{$this->db->users} AS u" )
		            ->join( "{$this->posts} AS p", 'p.post_author = u.ID', 'INNER' )
		            ->wheres( [
			            'p.post_type = %s'   => 'news',
			            'p.post_status = %s' => 'publish',
		            ] )
		            ->result();
		return $users;
	}

	/**
	 * ユーザー名を更新する
	 *
	 * @param string $login
	 * @param string $nicename
	 * @param int $id
	 *
	 * @return false|int
	 */
	public function update_login( $login, $nicename, $id ) {
		return $this->update( [
			'user_login'    => $login,
			'user_nicename' => $nicename,
		], [ 'ID' => $id ], [ '%s', '%s' ], [ '%d' ] );
	}
}
