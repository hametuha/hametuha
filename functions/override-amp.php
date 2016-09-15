<?php


/**
 * ニュース以外はamp無効
 */
add_filter( 'amp_skip_post', function( $skip, $post_id, $post ){
	return 'news' !== $post->post_type;
}, 10, 3 );


/**
 * タイトルを変更
 *
 * @todo get_document_titleが標準になったら消す
 * @param array
 */
add_filter( 'document_title_parts', function( $title ){
	if ( is_singular( 'news' ) ) {
		$title = [ hamenew_copy( get_the_title() ) ];
	}
	return $title;
} );

/**
 * タイトルタグのセパレータを変更
 */
add_filter( 'document_title_separator', function(){
	return '|';
} );

add_action( 'amp_post_template_head', function () {

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

add_action( 'amp_post_template_css', function ( $amp_template ) {
	// only CSS here please...
	$url = get_stylesheet_directory_uri().'/assets/img/hamenew-logo.png';
	echo <<<CSS
nav.amp-wp-title-bar {
	padding: 0 10px;
	background: #f9f9f9;
}
nav.amp-wp-title-bar a {
	background: transparent url( '{$url}' ) center 10px no-repeat;
	background-size: 90px 50px;
	display: block;
	padding: 65px 0 10px;
	width: 100%;
	margin: 0 auto;
	color: #666;
	text-align: center;	
}

nav.amp-wp-title-bar div {
	line-height: 1.1;
	font-size: 12px;
}
.amp-wp-content{
	color: #222;
	border-top: 1px solid #ddd;
}
.tmkm-amazon-view{
	border-top: 3px double #ddd;
	border-bottom: 3px double #ddd;
	margin: 10px 0;
	padding: 10px;
	font-size :0.85em;
}
.tmkm-amazon-img amp-img{
	margin: 0 auto;
}
.tmkm-amazon-view p{
	margin: 0.25em 0;
}
.amp-footer-content{
	text-align: center;
	font-size: 12px;
	color: #fff;
	background: #252E34;
	padding: 20px;
}
body{
font-family: "游ゴシック体", "Yu Gothic", YuGothic, sans-serif;
padding-bottom: 0;
}
.amp-ad-container{
margin: 10px -16px;
text-align: center;
}
footer p{
	margin: 0;
}
CSS;
} );


/**
 * 画像を追加する
 */
add_action( 'pre_amp_render_post', function () {
	add_filter( 'the_content', function ( $content ) {
		// 広告追加
		$ad = <<<HTML
<div class="amp-ad-container">
<amp-ad
 	type="adsense"
 	data-ad-client="ca-pub-0087037684083564"
 	data-ad-slot="9464744841"
 	width="320"
 	height="100">
</amp-ad>
</div>
HTML;
		$content = $ad.$content;
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
		// 関連記事
		$related = hamenew_related();
		if ( $related ) {
			ob_start();
			?>
			<h2>関連記事</h2>
			<ul class="amp-related">
				<?php foreach ( $related as $relate ) : ?>
				<li>
					<a href="<?= get_permalink( $relate ) ?>"><?= get_the_title( $relate ) ?></a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php
			$content .= ob_get_contents();
			ob_end_clean();
		}


		return $content;
	} );
} );

/**
 * サイトの情報を変更
 */
add_filter( 'amp_post_template_data', function ( $data ) {
	$data['blog_name'] = '破滅派がお送りする文学関連ニュース';
	$data['home_url'] = get_post_type_archive_link( 'news' );
	return $data;
} );

/**
 * ロゴ追加
 */
add_filter( 'amp_post_template_metadata', function ( $data ){

	$data['publisher']['logo'] = [
		'@type' => 'ImageObject',
		'url' => get_stylesheet_directory_uri().'/assets/img/ogp/hamenew-company.png',
		'height' => 60,
		'width' => 600,
	];
	$data['@type'] = 'NewsArticle';
	if ( ! isset( $data['image'] ) ) {
		$data['image'] = [
			'@type' => 'ImageObject',
			'url' => get_template_directory_uri().'/assets/img/ogp/hamenew-ogp.png',
			'width' => 1200,
			'height' => 696,
		];
	}
	$data['description'] = preg_replace( '#[\r\n]#', '', get_the_excerpt() );
	return $data;
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
