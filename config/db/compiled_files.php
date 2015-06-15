<?php
/**
 * ユーザーとタグを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

    'name' => 'compiled_files',

    'version' => '1.0.1',

    'columns' => [
	    'file_id' => [
		    'type' => Column::BIGINT,
		    'primary' => true,
		    'auto_increment' => true,
	    ],
        'type' => [
            'type' => Column::VARCHAR,
            'length' => 20,
        ],
        'post_id' => [
            'type' => Column::BIGINT,
            'signed' => false,
        ],
        'name'  => [
	        'type' => Column::VARCHAR,
            'length' => 256,
        ],
        'updated' => [
            'type' => Column::DATETIME,
        ],
    ],

    'indexes' => [
        'post_only' => ['post_id'],
        'type_post' => ['type', 'post_id']
    ]
];
