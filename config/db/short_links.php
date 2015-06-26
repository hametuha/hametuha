<?php
/**
 * ユーザーとタグを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

    'name' => 'short_links',

    'version' => '1.0',

    'columns' => [
	    'link_id' => [
		    'type' => Column::BIGINT,
		    'primary' => true,
	        'auto_increment' => true,
	    ],
        'url' => [
            'type' => Column::VARCHAR,
            'length' => 256,
        ],
	    'host' => [
		    'type' => Column::VARCHAR,
		    'length' => 256,
	    ],
	    'path' => [
		    'type' => Column::VARCHAR,
		    'length' => 256,
	    ],
	    'args' => [
		    'type' => Column::VARCHAR,
		    'length' => 256,
	    ],
        'created' => [
            'type' => Column::DATETIME,
        ],
    ],

    'indexes' => [
        'url' => ['url(20)']
    ]
];
