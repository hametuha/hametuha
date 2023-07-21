<?php
/**
 * ユーザーとタグを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'term_user_relationships',

	'version' => '1.0',

	'columns' => [
		'user_id'          => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'object_id'        => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'term_taxonomy_id' => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'updated'          => [
			'type' => Column::DATETIME,
		],
	],

	'indexes' => [
		'user'        => [ 'user_id', 'object_id', 'term_taxonomy_id' ],
		'tagged_date' => [ 'updated', 'object_id', 'user_id' ],
	],
];
