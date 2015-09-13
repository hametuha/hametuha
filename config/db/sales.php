<?php
/**
 * ユーザーとタグを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'sales',
	'version' => '1.0.5',
	'columns' => [
		'store'     => [
			'type'   => Column::VARCHAR,
			'length' => 24,
		],
		'date'      => [
			'type' => Column::DATE,
		],
		'asin'      => [
			'type'   => Column::VARCHAR,
			'length' => 24,
		],
		'place'     => [
			'type'   => Column::VARCHAR,
			'length' => 64,
		],
		'type'      => [
			'type'   => Column::VARCHAR,
			'length' => 128,
		],
		'unit'      => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'royalty'   => [
			'type'      => Column::DECIMAL,
			'max_digit' => 10,
			'float'     => 2,
		],
		'currency'  => [
			'type'   => Column::VARCHAR,
			'length' => 64,
		],
	],
	'indexes' => [
		'by_asin' => [ 'asin(6)' ],
		'by_date' => [ 'date', 'type' ],
		'by_type' => [ 'type', 'store' ],
	],
	'unique'  => [
		'record' => [ 'store', 'date', 'asin', 'place', 'type' ],
	],
];
