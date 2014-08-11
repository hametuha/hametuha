<?php
/**
 * テスト用ユーザー
 */

use WPametu\DB\Column;

$table = [

    'version' => '1.0',

    'columns' => [

        'ID' => [
            'type' => Column::INT,
            'auto_increment' => true,
        ],

        'group_id' => [
            'type' => Column::INT,
            'length' => 11,
        ],

        'name' => [
            'type' => Column::VARCHAR,
            'length' => 20,
        ],

    ],

    'primary_key' => ['ID', 'name'],

    'indexes' => [
        'title_index' => ['name'],
    ]
];
