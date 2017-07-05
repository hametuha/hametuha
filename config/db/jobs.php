<?php
/**
 * ジョブを登録する
 */

use WPametu\DB\Column;

$table = [

    'name' => 'jobs',

    'version' => '1.0.1',

    'columns' => [
	    'job_id' => [
		    'type' => Column::BIGINT,
		    'primary' => true,
		    'auto_increment' => true,
	    ],
        'title' => [
            'type' => Column::VARCHAR,
            'length' => 256,
        ],
        'owner_id' => [
            'type' => Column::BIGINT,
            'signed' => false,
        ],
        'issuer_id' => [
	        'type' => Column::BIGINT,
	        'signed' => false,
        ],
        'status'  => [
	        'type' => Column::VARCHAR,
            'length' => 20,
        ],
        'created' => [
        	'type' => Column::DATETIME,
        ],
        'updated' => [
            'type' => Column::DATETIME,
        ],
        'expires' => [
        	'type' => Column::DATETIME,
        ],
    ],
    'indexes' => [
        'by_owner'   => ['owner_id', 'status'],
        'by_issuer'   => ['issuer_id', 'status'],
        'by_datetime' => ['created', 'status', 'owner_id'],
        'expired' => ['expires', 'status'],
    ]
];
