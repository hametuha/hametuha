<?php
/**
 * ユーザーとタグを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'user_content_relationships',

	'version' => '1.0',

	'columns' => [
		'ID'        => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'rel_type'  => [
			'type'    => Column::VARCHAR,
			'length'  => 10,
			'default' => 'favorite',
		],
		'object_id' => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'user_id'   => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'location'  => [
			'type'      => Column::DECIMAL,
			'max_digit' => 10,
			'float'     => 9,
		],
		'content'   => [
			'type' => Column::TEXT,
		],
		'updated'   => [
			'type'    => Column::DATETIME,
			'default' => '0000-00-00 00:00:00',
		],
	],

	'indexes' => [
		'user'         => [ 'rel_type', 'object_id', 'user_id' ],
		'favored_date' => [ 'updated', 'object_id', 'user_id' ],
	],
];
