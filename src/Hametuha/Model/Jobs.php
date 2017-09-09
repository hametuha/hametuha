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
 */
class Jobs extends Model {

	protected $name = 'jobs';

	protected $primary_key = 'job_id';

	protected $related = [ 'posts', 'users', 'object_relationships', 'job_meta' ];

	protected $updated_column = 'updated';

	protected $default_placeholder = [
		'job_id'    => '%d',
		'job_key'   => '%s',
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
	 * @param string $job_key
	 * @param string $title
	 * @param string $expires
	 * @param int    $owner_id
	 * @param int    $issuer_id
	 * @param string $status
	 * @param array  $metas
	 *
	 * @return \WP_Error|\stdClass
	 */
	public function add( $job_key, $title, $expires = '', $owner_id = 0, $issuer_id = 0, $status = JobStatus::ONGOING, array $metas = [] ) {
		$now = current_time( 'mysql' );
		$data = [
			'job_key'  => $job_key,
			'title'    => $title,
			'owner_id' => $owner_id,
			'issuer_id' => $issuer_id,
		    'status'   => $status,
		    'created'  => $now,
		];
		if ( $expires ) {
			$data['expires'] = $expires;
		}
		if ( ! $this->insert( $data ) ) {
			return new \WP_Error( 500, 'ジョブの追加に失敗しました。' );
		}
		$job_id = $this->db->insert_id;
		if ( $metas ) {
			$this->job_meta->add( $job_id, $metas );
		}
		return $this->get( $job_id );
	}

	/**
	 *
	 *
	 * @param int    $job_id
	 * @param string $new_status
	 *
	 * @return bool|\WP_Error
	 */
	public function update_status( $job_id, $new_status ) {
		$job = $this->get( $job_id );
		if ( ! $job ) {
			return new \WP_Error( 404, 'ジョブが見つかりませんでした' );
		}
		$old_status = $job->status;
		if ( $new_status === $old_status ) {
			return new \WP_Error( 400, 'ジョブのステータスに変化がありません。' );
		}
		$updated = $this->update( [
			'status' => $new_status,
		], [
			'job_id' => $job_id,
		] );
		if ( $updated ) {
			do_action( 'hametuha_job_updated', $job, $new_status, $old_status );
			return true;
		} else {
			return new \WP_Error( 500, 'ジョブステータスの更新に失敗しました' );
		}
	}

	/**
	 * Get single job
	 *
	 * @param int $job_id
	 * @param bool $ignore_cache
	 *
	 * @return mixed|null
	 */
	public function get( $job_id, $ignore_cache = false ) {
		$job = parent::get( $job_id, $ignore_cache );
		if ( ! $job ) {
			return $job;
		}
		$job->meta = $this->job_meta->all_metas( $job->job_id );
		return $job;
	}

	/**
	 * Remove job
	 *
	 * @param int $job_id
	 *
	 * @return false|int
	 */
	public function remove( $job_id ) {
		if ( $this->delete_where( [ 'job_id' => $job_id ] ) ) {
			return $this->job_meta->delete_where( [
				'job_id' => $job_id,
			] );
		} else {
			return false;
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
			default:
				return parent::__get( $name );
				break;
		}
	}
}
