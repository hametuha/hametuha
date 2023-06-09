<?php
/**
 * File meta table
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'compiled_file_meta',

	'version' => '1.0.0',

	'columns' => [
		'meta_id'    => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'file_id'    => [
			'type' => Column::BIGINT,
		],
		'meta_key'   => [
			'type'   => Column::VARCHAR,
			'length' => 20,
		],
		'meta_value' => [
			'type' => Column::LONGTEXT,
		],
		'created'    => [
			'type' => Column::DATETIME,
		],
		'updated'    => [
			'type' => Column::DATETIME,
		],
	],

	'indexes' => [
		'by_id'     => [ 'file_id', 'meta_key' ],
		'key_value' => [ 'meta_key(20)', 'meta_value(200)' ],
	],
];
