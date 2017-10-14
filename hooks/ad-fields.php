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
	];
} );

// Regoster analytics
add_action( 'taf_head', '_hametuha_ga_code' );
