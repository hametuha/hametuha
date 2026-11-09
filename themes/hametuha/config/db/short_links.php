<?php
/**
 * Handle short link URL for misc actions.
 */

use WPametu\DB\Column;

$table = [

	'name'    => 'short_links',

	'version' => '1.1.0',

	'columns' => [
		'link_id' => [
			'type'           => Column::BIGINT,
			'primary'        => true,
			'auto_increment' => true,
		],
		'url'     => [
			'type'   => Column::VARCHAR,
			'length' => 256,
		],
		'host'    => [
			'type'   => Column::VARCHAR,
			'length' => 256,
		],
		'path'    => [
			'type'   => Column::VARCHAR,
			'length' => 256,
		],
		'args'    => [
			'type'   => Column::VARCHAR,
			'length' => 256,
		],
		'created' => [
			'type' => Column::DATETIME,
		],
		'expires' => [
			'type'    => Column::DATETIME,
			'default' => '0000-00-00 00:00:00',
		],
		'file_id' => [
			'type'    => Column::BIGINT,
			'default' => 0,
			'signed'  => false,
		],
	],

	// url は先頭が共通（https://hametuha.com/...）で、末尾の utm_* だけが違う。
	// 先頭 N 文字のインデックスでは絞り込めず全件スキャンになるので、全体に張る（#467）。
	// version を上げずに変えたので、既存の環境には手動で ALTER TABLE する（#467 参照）。
	'indexes' => [
		'url' => [ 'url' ],
	],
];
