<?php
/**
 * Advertisement fields.
 */

/**
 * Register fields
 */
add_filter( 'taf_default_positions', function() {
	return [
		'fb-after-header' => [
			'name' => 'Facebookのタイトル直下',
			'description' => 'Facebook Instant Articleのタイトル直下に表示されます。',
			'mode' => 'iframe',
		],
		'fb-after-content' => [
			'name' => 'Facebookのコンテンツ下',
			'description' => 'Facebook Instant Articleのコンテンツ直下に表示されます。',
			'mode' => 'iframe',
		],
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

// Regoster analytics
add_action( 'taf_head', '_hametuha_ga_code' );
