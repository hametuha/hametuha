<?php
/**
 * ユーザーとタグを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'notifications',

	'version' => '1.0.1',

	'columns' => [
		'notification_id' => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'recipient_id'    => [
			'type' => Column::BIGINT,
		],
		'type'            => [
			'type'   => Column::VARCHAR,
			'length' => 20,
		],
		'object_id'       => [
			'type'   => Column::BIGINT,
			'signed' => false,
		],
		'message'         => [
			'type' => Column::LONGTEXT,
		],
		'avatar'          => [
			'type'   => Column::VARCHAR,
			'length' => 256,
		],
		'created'         => [
			'type' => Column::DATETIME,
		],
	],
	'indexes' => [
		'recipients' => [ 'recipient_id', 'type' ],
		'date'       => [ 'created', 'recipient_id' ],
	],
];
