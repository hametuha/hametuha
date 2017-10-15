<?php
if ( ! is_feed() && is_singular() ) {
	the_post();
	add_filter( 'the_content', '_fb_instant_content', 11 );
}
/** @var WP_Post $post */
?><!doctype html>
<html lang="ja" prefix="op: http://media.facebook.com/op#">
<head>
	<meta charset="utf-8">
	<link rel="canonical" href="<?php the_permalink() ?>">
	<meta property="op:markup_version" content="v1.0">
	<?php
	$category = [];
	foreach ( [ 'genre', 'nouns' ] as $taxonomy ) {
		$terms = get_the_terms( $post, $taxonomy );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$category = array_merge( $category, $terms );
		}
	}
	if ( $category ) :
		$categories = array_map( function ( $cat ) {
			return $cat->name;
		}, $category );
		?>
		<meta property="op:tags" content="<?= esc_attr( implode( ', ', $categories ) ) ?>">
	<?php endif; ?>
</head>
<body>
<article>
	<header>
		<!-- The title and subtitle shown in your Instant Article -->
		<h1><?php the_title() ?></h1>

		<!-- The date and time when your article was originally published -->
		<time class="op-published" datetime="<?php the_time( DATE_ATOM ) ?>"><?php the_time( 'Y.m.d H:i' ) ?></time>

		<!-- The date and time when your article was last updated -->
		<time class="op-modified"
		      dateTime="<?php the_modified_date( DATE_ATOM ) ?>"><?php the_modified_date( 'Y.m.d H:i' ) ?></time>

		<!-- The authors of your article -->
		<address>
			<a rel="facebook" href="https://www.facebook.com/minicome/"><?php the_author() ?></a>
		</address>

		<!-- The cover image shown inside your article -->
		<?php if ( has_post_thumbnail() ) : $thumbnail = get_post( get_post_thumbnail_id() ) ?>
			<figure>
				<img src="<?= get_the_post_thumbnail_url( null, 'large' ) ?>"/>
				<?php if ( $thumbnail->post_excerpt ) : ?>
					<figcaption><?= wp_kses( $thumbnail->post_excerpt, [ 'a' => [ 'href' ] ] ) ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

	</header>

    <figure class="op-ad">
        <iframe width="320" height="50" src="<?= taf_iframe_url( 'fb-after-header', [
			'width'        => 320,
			'height'       => 50,
            'utm_source'   => 'facebook',
            'utm_medium'   => 'Social Instant Article',
            'utm_campaign' => 'after-header',
        ] ) ?>"></iframe>
    </figure>

	<!-- Excerpt -->
	<?php if ( has_excerpt() ) : ?>
		<aside>
			<?= esc_html( strip_tags( get_the_excerpt() ) ) ?>
		</aside>
	<?php endif; ?>

	<!-- Body text for your article -->
	<?php the_content(); ?>

	<?php if ( $links = hamenew_books() ) : ?>
        <h2>この記事の関連書籍など</h2>
        <ul>
            <?php foreach ( $links as list( $title, $url, $src, $author, $publisher, $rank ) ) : ?>
                <li>
                    <a href="<?= esc_url( $url ) ?>">
						<strong><?= esc_html( $title ) ?></strong>
						<?php if ( $author ) : ?>
                            <?= esc_html( $author ) ?>
						<?php endif; ?>
						<?php if ( $publisher ) : ?>
							（<?= esc_html( $publisher ) ?>）
						<?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
	<?php endif; ?>



    <!-- An ad within your article -->
    <figure class="op-ad">
        <iframe width="300" height="250" style="border:0; margin:0;" src="<?= taf_iframe_url( 'fb-after-header', [
            'width'        => 300,
            'height'       => 250,
            'utm_source'   => 'facebook',
            'utm_medium'   => 'Social Instant Article',
            'utm_campaign' => 'after-content',
        ] )  ?>"></iframe>
    </figure>

	<?php if ( $links = hamenew_links() ) : ?>
        <h2> この記事の関連リンク</h2>
        <ol>
            <?php foreach ( $links as list( $title, $url ) ) : ?>
                <li>
                    <a href="<?= esc_url( $url ) ?>">
						<?= esc_html( $title ) ?>
                    </a>
                </li>
			<?php endforeach; ?>
        </ol>
	<?php endif; ?>


	<?php if ( $post->_event_title ) : ?>
		
		<h2><?= esc_html( $post->_event_title ) ?></h2>

		<?php if ( $post->_event_start || $post->_event_end ) : ?>
			<p>

				<?php if ( $post->_event_start ) : ?>
					<strong>日時: </strong> <?= hamenew_event_date( $post->_event_start, $post->_event_end ) ?>
					<?php if ( strtotime( $post->_event_end ?: $post->_event_start ) < current_time( 'timestamp', true ) ) : ?>
						<small>（終了しました）</small>
					<?php endif; ?>
				<?php elseif ( $post->_event_end ) : ?>
					<strong>〆切: </strong> <?= mysql2date( 'Y年n月j日（D）', $post->_event_end ) ?>
					<?php if ( strtotime( $post->_event_end ) < current_time( 'timestamp', true ) ) : ?>
						<small>（終了しました）</small>
					<?php endif; ?>
				<?php endif; ?>
			</p>
		<?php endif; ?>


		<?php if ( $post->_event_desc ) : ?>
			<?= wpautop( esc_html( $post->_event_desc ) ) ?>
		<?php endif; ?>

		<?php if ( $post->_event_address && ( $latlng = hametuha_geocode( $post->_event_address, 'post_' . get_the_ID() ) ) ) : ?>
			<?php if ( is_wp_error( $latlng ) ) : ?>
				<p>地図情報を取得できませんでした: <?= esc_html( $latlng->get_error_message() ) ?></p>
			<?php else : ?>
				<figure class="op-map">
					<figcaption><?= esc_html( $post->_event_address . ' ' . $post->_event_bld ) ?></figcaption>
					<script type="application/json" class="op-geotag">
						{
							"type": "Feature",
							"geometry": {
								"type": "Point",
								"coordinates": [<?= $latlng['lat'] ?>, <?= $latlng['lng'] ?> ]
							},
							"properties": {
								"title": "<?= esc_js( $post->_event_address ) ?>"
							}
						}
					</script>
				</figure>
			<?php endif; ?>
		<?php endif; ?>

	<?php endif; ?>

	<!-- Analytics code for your article -->
	<figure class="op-tracker">
		<iframe hidden>
			<script>
				(function (i, s, o, g, r, a, m) {
					i['GoogleAnalyticsObject'] = r;
					i[r] = i[r] || function () {
							(i[r].q = i[r].q || []).push(arguments)
						}, i[r].l = 1 * new Date();
					a = s.createElement(o), m = s.getElementsByTagName(o)
					0
					]
					;
					a.async = 1;
					a.src = g;
					m.parentNode.insertBefore(a, m)
				})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
				ga('create', 'UA-1766751-2', 'auto');
				ga('require', 'displayfeatures');
				ga('set', 'campaignSource', 'Facebook');
				ga('set', 'campaignMedium', 'Social Instant Article');
				ga('send', 'pageview', {
					title: '<?= esc_js( get_the_title() ) ?>',
					page : '<?= preg_replace( '#^https://hametuha\.(com|info)#', '', get_permalink() ) ?>'
				});
			</script>
		</iframe>
	</figure>

	<footer>
		<?php if ( $related = hamenew_related( 3 ) ) : ?>
			<!-- // Related posts -->
			<ul class="op-related-articles">
				<?php foreach ( $related as $rel ) : ?>
					<li><a href="<?= get_permalink( $rel ) ?>"><?= esc_html( get_the_title( $rel ) ) ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<!-- Copyright details for your article -->
		<small>&copy; 2007 Hametuha</small>
	</footer>
</article>
</body>
</html>
