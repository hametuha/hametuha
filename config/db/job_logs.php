<?php
/**
 * ジョブのログ
 */

use WPametu\DB\Column;

$table = [

    'name' => 'job_logs',

    'version' => '1.0.0',

    'columns' => [
	    'job_log_id' => [
		    'type' => Column::BIGINT,
		    'primary' => true,
		    'auto_increment' => true,
	    ],
        'job_id' => [
            'type'   => Column::BIGINT,
            'signed' => false,
        ],
        'message' => [
            'type' => Column::TEXT,
        ],
        'owner'  => [
	        'type' => Column::BIGINT,
	        'signed' => false,
        ],
        'is_error' => [
        	'type'    => Column::TINYINT,
        ],
        'created' => [
            'type' => Column::DATETIME,
        ],
    ],
    'indexes' => [
    	'by_parent'   => ['job_id', 'created'],
        'by_datetime' => ['created'],
    ]
];
