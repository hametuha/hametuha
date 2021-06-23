<?php
/**
 * Amp related hooks.
 *
 * @package hametuha
 */

/**
 * ニュース以外はamp無効
 */
add_filter( 'amp_skip_post', function( $skip, $post_id, $post ) {
	return 'news' !== $post->post_type;
}, 10, 3 );

/**
 * Add CSS
 *
 * @param AMP_Post_Template $amp_template
 */
add_action( 'amp_post_template_css', function ( AMP_Post_Template $amp_template ) {
	$img_root = get_template_directory_uri() . '/assets/img';
	$css_path = get_template_directory() . '/assets/css/amp.css';
	if ( file_exists( ! $css_path ) ) {
		return;
	}
	$css = file_get_contents( $css_path );
	$css = str_replace( '/*# sourceMappingURL=map/amp.css.map */', '', $css );
	echo str_replace( '../', $img_root, $css );
} );


/**
 * 画像を追加する
 */
add_action( 'pre_amp_render_post', function () {
	add_filter( 'the_content', function ( $content ) {
		// 記事の後の広告
		$content .= <<<HTML
<div class="amp-ad-container">
<amp-ad
 	type="adsense"
 	data-ad-client="ca-pub-0087037684083564"
 	data-ad-slot="3418211243"
 	width="300"
 	height="250">
</amp-ad>
</div>
HTML;
		return $content;
	} );
} );

/**
 * サイトの情報を変更
 */
add_filter( 'amp_post_template_data', function ( $data ) {
		$data['blog_name']                                      = 'はめにゅー';
		$data['home_url']                                       = get_post_type_archive_link( 'news' );
		$data['site_icon_url']                                  = get_template_directory_uri() . '/assets/img/ogp/minico-256x256.png';
		$data['customizer_settings']['header_color']            = '#000';
		$data['customizer_settings']['header_background_color'] = '#FBF0E4';
		return $data;
} );

/**
 * ロゴ追加
 */
add_filter( 'amp_post_template_metadata', function ( $data ) {

	$data['publisher']['logo'] = [
		'@type'  => 'ImageObject',
		'url'    => get_stylesheet_directory_uri() . '/assets/img/ogp/hamenew-company.png',
		'height' => 60,
		'width'  => 600,
	];
	$data['@type']             = 'NewsArticle';
	if ( ! isset( $data['image'] ) ) {
		$data['image'] = [
			'@type'  => 'ImageObject',
			'url'    => get_template_directory_uri() . '/assets/img/ogp/hamenew-ogp.png',
			'width'  => 1200,
			'height' => 696,
		];
	}
	$data['description'] = preg_replace( '#[\r\n]#', '', get_the_excerpt() );
	return $data;
} );


/**
 * AMPに要素を追加する
 */
add_action( 'amp_post_template_footer', function () {
	?>
<footer class="amp-footer-content">
	<p>&copy; 2007 Hametuha</p>
</footer>
	<?php
} );


/**
 * Google analyticsを追加
 */
add_filter( 'amp_post_template_analytics', function ( $analytics ) {
	if ( ! is_array( $analytics ) ) {
		$analytics = [];
	}

	// https://developers.google.com/analytics/devguides/collection/amp-analytics/
	$analytics['googleanalytics'] = [
		'type'        => 'googleanalytics',
		'attributes'  => [
			// 'data-credentials' => 'include',
		],
		'config_data' => [
			'vars'     => [
				'account' => 'UA-1766751-2',
			],
			'triggers' => [
				'trackPageview' => [
					'on'      => 'visible',
					'request' => 'pageview',
				],
			],
		],
	];

	return $analytics;
} );
