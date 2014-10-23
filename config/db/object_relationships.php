<?php
/**
 * オブジェクトとオブジェクトを紐づけるテーブル
 */

use WPametu\DB\Column;

$table = [

    'name' => 'object_relationships',

    'version' => '1.1',

    'columns' => [
	    'ID' => [
		    'type' => Column::BIGINT,
		    'primary' => true,
		    'auto_increment' => true,
	    ],
	    'rel_type' => [
		    'type' => Column::VARCHAR,
		    'length' => 64,
		    'default' => 'list'
	    ],
        'subject_id' => [
            'type' => Column::BIGINT,
            'signed' => false,
        ],
        'object_id' => [
            'type' => Column::BIGINT,
            'signed' => false,
        ],
        'created' => [
            'type' => Column::TIMESTAMP,
        ]
    ],

    'indexes' => [
        'grab' => ['rel_type', 'subject_id', 'object_id'],
        'seek_object' => ['rel_type', 'object_id']
    ],

	'charset' => 'utf8',
];
