<?php

/**
 * RSS2.0にメディアを追加する
 *
 * @see http://www.rssboard.org/media-rss
 */
add_action( 'rss2_ns', function () {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"';
} );


/**
 * RSSに追加情報をくっつける
 */
add_action( 'rss2_item', function () {
	$series = \Hametuha\Model\Series::get_instance();
	// サムネイル
	if ( has_post_thumbnail() ) {
		$src   = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' )[0];
		$title = get_the_title_rss();
		echo <<<XML
		<media:thumbnail url="{$src}" />
		<media:content url="{$src}" medium="image">
			<media:title type="html">{$title}</media:title>
		</media:content>
XML;
	}
	if ( 'series' == get_post_type() ) {
		//  KDP ready
		if ( 2 === $series->get_status( get_the_ID() ) ) {
			$asin     = $series->get_asin( get_the_ID() );
			$subtitle = esc_html( $series->get_subtitle( get_the_ID() ) );
			echo <<<XML
				<category>{$subtitle}</category>
				<dc:identifier>{$asin}</dc:identifier>
				<dc:relation>http://www.amazon.co.jp/dp/{$asin}/?t=hametuha-22</dc:relation>
XML;
		}
	}
} );
