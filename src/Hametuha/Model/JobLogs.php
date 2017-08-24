<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

class JobLogs extends Model {

	/**
	 * @var array
	 */
	protected $default_placeholder = [
		'job_id'   => '%d',
		'message'  => '%s',
		'owner'    => '%d',
		'is_error' => '%d',
		'created'  => '%s',
	];

	/**
	 * Add job error
	 *
	 * @param int    $job_id
	 * @param string $message
	 * @param int    $owner
	 * @param bool   $error
	 *
	 * @return bool
	 */
	public function add( $job_id, $message, $owner = 0, $error = false ) {
		return (bool) $this->insert( [
			'job_id'   => $job_id,
		    'message'  => $message,
		    'owner'    => $owner,
		    'is_error' => (int) $error,
		    'created'  => current_time( 'mysql' ),
		] );
	}

}
