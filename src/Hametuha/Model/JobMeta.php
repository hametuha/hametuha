<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

class JobMeta extends Model {

	/**
	 * @var array
	 */
	protected $default_placeholder = [
		'job_meta_id'   => '%d',
		'job_id' => '%d',
		'meta_key'  => '%s',
		'meta_value'    => '%d',
		'created'  => '%s',
	];

	/**
	 * Add job error
	 *
	 * @param int   $job_id
	 * @param array $values
	 *
	 * @return bool
	 */
	public function add( $job_id, array $values ) {
		$job_id = (int) $job_id;
		$input = [];
		$created = current_time( 'mysql' );
		foreach ( $values as $key => $val ) {
			$input[] = [
				$job_id,
				$this->db->prepare( '%s', $key ),
				$this->db->prepare( '%s', $val ),
				$created,
			];
		}
		$input = implode( ', ', array_map( function( $row ) {
			return '(' . implode( ', ', $row ) . ' )';
		}, $input ) );
		$query = <<<SQL
			INSERT INTO {$this->table} ( job_id, meta_key, meta_value, created )
			VALUES {$input}
SQL;
		return (bool) $this->db->query( $query );
	}

	/**
	 * Add log for job
	 *
	 * @param int    $job_id
	 * @param string $message
	 *
	 * @return false|int
	 */
	public function log( $job_id, $message ) {
		return $this->db->insert( $this->table, [
			'job_id' => $job_id,
			'meta_key' => 'log',
			'meta_value' => $message,
			'created' => current_time( 'timestamp' ),
		] );
	}

}
