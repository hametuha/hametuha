<?php
/**
 * ジョブを登録する
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'jobs',

	'version' => '1.0.3.1',

	'columns' => [
		'job_id'    => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'job_key'   => [
			'type'   => Column::VARCHAR,
			'length' => 64,
		],
		'title'     => [
			'type'   => Column::VARCHAR,
			'length' => 256,
		],
		'owner_id'  => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'issuer_id' => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'status'    => [
			'type'   => Column::VARCHAR,
			'length' => 20,
		],
		'created'   => [
			'type' => Column::DATETIME,
		],
		'updated'   => [
			'type' => Column::DATETIME,
		],
		'expires'   => [
			'type' => Column::DATETIME,
		],
	],
	'indexes' => [
		'by_key_owner'    => [ 'job_key', 'owner_id', 'status' ],
		'by_issuer_key'   => [ 'job_key', 'issuer_id', 'status' ],
		'by_datetime_key' => [ 'job_key', 'created', 'status' ],
		'by_expired'      => [ 'expires', 'status', 'job_key' ],
	],
];
