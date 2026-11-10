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

	// 本番の実体に合わせてある（#469）。version を上げずに変えたので、既存の環境には手動で ALTER TABLE する。
	// - user: 本番は (user_id, object_id) の2列だった。定義を本番に揃える
	// - object_term: 作品ごとのタグ集計（Model\Review::get_post_chart_points()）用
	// - term_object: タグごとの作者一覧（QueryHighJack\AuthorReviewQuery）用
	'indexes' => [
		'user'        => [ 'user_id', 'object_id' ],
		'tagged_date' => [ 'updated', 'object_id', 'user_id' ],
		'object_term' => [ 'object_id', 'term_taxonomy_id' ],
		'term_object' => [ 'term_taxonomy_id', 'object_id' ],
	],
];
