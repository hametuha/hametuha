<?php
/**
 * AMP関連関数
 *
 * @package hametuha
 */


// Stop AMP customizer because it causes slow query.
add_filter( 'amp_customizer_is_enabled', '__return_false' );

/**
 * ニュース以外はamp無効
 */
add_filter(
	'amp_skip_post',
	function ( $skip, $post_id, $post ) {
		return 'news' !== $post->post_type;
	},
	10,
	3
);

/**
 * amp用サイトマップ
 */
add_action(
	'do_feed_amp_sitemap',
	function () {
		header( 'Content-Type: text/xml; charset=UTF-8' );
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		?>
	<urlset
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
		xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<url>
				<loc><?php echo trailingslashit( get_permalink() ); ?>amp/</loc>
				<lastmod><?php the_modified_time( DateTime::W3C ); ?></lastmod>
				<changefreq>weekly</changefreq>
				<priority>0.5</priority>
			</url>
		<?php endwhile; ?>
	</urlset>
		<?php
	}
);
