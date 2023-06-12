<?php
/**
 * ジョブのログ
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'job_meta',

	'version' => '1.0.0',

	'columns' => [
		'job_meta_id' => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'job_id'      => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'meta_key'    => [
			'type'   => Column::VARCHAR,
			'length' => 64,
		],
		'meta_value'  => [
			'type' => Column::LONGTEXT,
		],
		'created'     => [
			'type' => Column::DATETIME,
		],
	],
	'indexes' => [
		'by_parent' => [ 'job_id', 'meta_key' ],
		'by_key'    => [ 'meta_key(64)', 'meta_value(192)' ],
	],
];
