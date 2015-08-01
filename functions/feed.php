<?php

/**
 * RSS2.0にメディアを追加する
 */
add_action( 'rss2_ns', function () {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"';
} );


/**
 * サムネイルをメディアタグに書き出す
 */
add_action( 'rss2_item', function () {
	if ( has_post_thumbnail() ) {
		$src   = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' )[0];
		$title = get_the_title_rss();
		echo <<<EOS
		<media:thumbnail url="{$src}" />
		<media:content url="{$src}" medium="image">
			<media:title type="html">{$title}</media:title>
		</media:content>

EOS;
	}
} );
