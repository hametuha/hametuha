<?php

namespace Hametuha\Model;


use Hametuha\Master\JobStatus;
use WPametu\DB\Model;

/**
 * Job model
 *
 * @package hametuha
 * @property-read string $users
 * @property-read string $posts
 * @property-read string $object_relationships
 * @property-read JobMeta $job_meta
 * @property-read JobLogs $job_log
 */
class Jobs extends Model {

	protected $related = [ 'posts', 'users', 'object_relationships', 'job_meta', 'job_log' ];

	protected $updated_column = 'updated';

	protected $default_placeholder = [
		'job_id'    => '%d',
		'title'     => '%s',
		'owner_id'  => '%d',
		'issuer_id' => '%d',
		'status'    => '%s',
	    'created'   => '%s',
		'updated'   => '%s',
		'expires'   => '%s',
	];

	/**
	 * Create new Job
	 *
	 * @param string $title
	 * @param bool $expires
	 * @param int $owner_id
	 * @param array $related_posts
	 * @param array $related_users
	 * @param string $status
	 *
	 * @return \WP_Error|\stdClass
	 */
	public function add( $title, $expires = false, $owner_id = 0, $related_posts = [], $related_users = [], $status = JobStatus::ONGOING ) {
		$job_id = $this->insert( [
			'title'    => $title,
			'owner_id' => $owner_id,
		    'status'   => $status,
		    'created'  => current_time( 'mysql' ),
		] );
		if ( ! $job_id ) {
			return new \WP_Error( 500, 'ジョブの追加に失敗しました。' );
		}
		
		else {
			return $this->get( $job_id );
		}
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'job_meta':
				return JobMeta::get_instance();
				break;
			case 'job_logs':
				return JobLogs::get_instance();
				break;
			defautl:
				return parent::__get( $name );
				break;
		}
	}
}
