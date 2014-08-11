<?php
/**
 * テスト用テーブル
 */

use WPametu\DB\Column;

$table = [

    'version' => '1.0',

    'columns' => [

        'ID' => [
            'type' => Column::INT,
            'primary' => true,
            'auto_increment' => true,
        ],

        'test_title' => [
            'type' => Column::VARCHAR,
            'length' => 20
        ],

        'test_price' => [
            'type' => Column::DECIMAL,
            'max_digit' => 10,
            'float' => 3
        ]

    ],

    'indexes' => [
        'title_index' => ['test_title'],
    ]
];
