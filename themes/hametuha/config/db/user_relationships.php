<?php
/**
 * オブジェクトとオブジェクトを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'user_relationships',

	'version' => '1.0',

	'columns' => [
		'ID'        => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'user_id'   => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'target_id' => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'status'    => [
			'type'    => Column::TINYINT,
			'signed'  => false,
			'default' => 1,
		],
		'created'   => [
			'type' => Column::DATETIME,
		],
		'updated'   => [
			'type' => Column::DATETIME,
		],
	],

	'indexes' => [
		'list'    => [ 'user_id', 'status' ],
		'reverse' => [ 'target_id', 'status' ],
	],
	'unique'  => [
		'user' => [ 'user_id', 'target_id' ],
	],
	'charset' => 'utf8',
];
