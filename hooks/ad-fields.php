<?php
/**
 * Advertisement fields.
 */

/**
 * Register fields
 */
add_filter( 'taf_default_positions', function() {
	return [
		'dashboard-analytics-footer' => [
			'name' => 'アクセス解析',
			'description' => 'アクセス解析ページの一番下に表示されます。',
		],
		'dashboard-sales-footer' => [
			'name' => '売上',
			'description' => '売上ページの一番上に表示されます。',
		],
	];
} );

