<?php
/**
 * Handle short link URL for misc actions.
 */

use WPametu\DB\Column;

$table = [

    'name' => 'short_links',

    'version' => '1.1.0',

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
		'expires' => [
			'type'    => Column::DATETIME,
			'default' => '0000-00-00 00:00:00',
		],
		'file_id' => [
			'type' => Column::BIGINT,
			'default' => 0,
			'signed' => false,
		],
    ],

    'indexes' => [
        'url'       => [ 'url(20)' ],
		'url_owner' => [ 'url(20)', 'owner(200)' ],
    ]
];
