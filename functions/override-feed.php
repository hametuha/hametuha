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
		$thum_id = get_post_thumbnail_id();
		$full    = wp_get_attachment_image_src( $thum_id, 'full' );
		$title   = esc_attr( apply_filters( 'the_title_rss', get_the_title( $thum_id ) ) );
		echo <<<XML
		<media:thumbnail url="{$full[0]}" />
		<media:group>
			<media:content size="full" url="{$full[0]}" medium="image" width="{$full[1]}" height="{$full[2]}">
				<media:title type="plain">{$title}</media:title>
			</media:content>
XML;
		foreach ( [ 'large', 'medium', 'thumbnail' ] as $size ) {
			$image = wp_get_attachment_image_src( $thum_id, $size );
			if ( ! $image ) {
				continue;
			}
			echo <<<XML
				<media:content size="{$size}" url="{$image[0]}" medium="image" width="{$image[1]}" height="{$image[2]}">
					<media:title type="plain">{$title}</media:title>
				</media:content>
XML;
		}
		echo <<<XML
		</media:group>
XML;
	}
	if ( 'series' == get_post_type() ) {
		//  KDP ready
		if ( 2 === $series->get_status( get_the_ID() ) ) {
			$asin     = $series->get_asin( get_the_ID() );
			$subtitle = esc_html( $series->get_subtitle( get_the_ID() ) );
			$url      = $series->get_kdp_url( get_the_ID() );
			echo <<<XML
				<category>{$subtitle}</category>
				<dc:identifier>{$asin}</dc:identifier>
				<dc:relation>{$url}</dc:relation>
XML;
		}
	}
} );
