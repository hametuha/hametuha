<?php
/**
 * ユーザーの売上を記録するテーブル
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'user_sales',

	'version' => '1.0.2',

	'columns' => [
		'sales_id'    => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'sales_type'  => [
			'type'   => Column::VARCHAR,
			'length' => 48,
		],
		'user_id'     => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'price'       => [
			'type'   => Column::FLOAT,
			'signed' => false,
		],
		'unit'        => [
			'type'   => Column::INT,
			'signed' => false,
		],
		'tax'         => [
			'type'   => Column::FLOAT,
			'signed' => false,
		],
		'deducting'   => [
			'type'   => Column::FLOAT,
			'signed' => false,
		],
		'total'       => [
			'type'   => Column::FLOAT,
			'signed' => false,
		],
		'status'      => [
			'type'   => Column::TINYINT,
			'signed' => true,
		],
		'description' => [
			'type' => Column::TEXT,
		],
		'created'     => [
			'type' => Column::DATETIME,
		],
		'fixed'       => [
			'type' => Column::DATETIME,
		],
		'updated'     => [
			'type' => Column::DATETIME,
		],
	],

	'indexes' => [
		'type_user' => [ 'sales_type', 'user_id', 'created' ],
		'by_user'   => [ 'user_id', 'created' ],
		'by_date'   => [ 'created', 'status' ],
	],
];
