<?php if ( $links = hamenew_links() ) : ?>
<h2 class="amp-hametuha-title">関連リンク</h2>
<ul class="amp-hametuha-links">
	<?php foreach ( $links as list( $title, $url ) ) : ?>
		<li class="amp-hametuha-links-item">
			<a href="<?php echo esc_url( $url ); ?>" class="amp-hametuha-links-link">
				<?php echo esc_html( $title ); ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if ( $links = hamenew_books() ) : ?>
	<h2 class="amp-hametuha-title">関連書籍</h2>
	<ul class="amp-hametuha-books">
		<?php foreach ( $links as list( $title, $url, $src, $author, $publisher, $rank ) ) : ?>
			<li class="amp-hametuha-books-item">
				<a href="<?php echo esc_url( $url ); ?>" class="amp-hametuha-books-link">
					<?php if ( $src ) : ?>
					<amp-img src="<?php echo $src; ?>" alt="<?php echo esc_attr( $title ); ?>" class="amp-hametuha-books-img"
							 width="66" height="100"
							 layout="responsive"></amp-img>
						<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<p class="amp-hametuha-muted">
		Supported by amazon Product Advertising API
	</p>
<?php endif; ?>


<?php
// 関連記事
$related = hamenew_related();
if ( $related ) {
	?>
	<h2 class="amp-hametuha-title">関連記事</h2>
	<ul class="amp-hametuha-related">
		<?php foreach ( $related as $relate ) : ?>
			<li class="amp-hametuha-related-item">
				<a href="<?php echo get_permalink( $relate ); ?>" class="amp-hametuha-related-link">

					<?php if ( has_post_thumbnail( $relate ) ) : ?>
						<div class="amp-hametuha-related-img">
						<?php
							$src = wp_get_attachment_image_src( get_post_thumbnail_id( $relate ), 'thumbnail' );
							printf(
								'<amp-img alt="%1$s" src="%2$s" width="%3$d" height="%4$d" layout="responsive"></amp-img>',
								esc_attr( get_the_title( $relate ) ),
								$src[0],
								$src[1],
								$src[2]
							);
						?>
						</div>
					<?php endif; ?>

					<div class="amp-hametuha-related-body">
						<h3 class="amp-hametuha-related-title">
							<?php echo get_the_title( $relate ); ?>
						</h3>
						<p class="amp-hametuha-related-meta">
							<?php
								echo mysql2date( 'Y.m.d', $relate->post_date );
								$genre = get_the_terms( $relate, 'genre' );
							if ( $genre && ! is_wp_error( $genre ) ) {
								echo ' | ' . implode(
									', ',
									array_map(
										function( $term ) {
											return esc_html( $term->name );
										},
										$genre
									)
								);
							}
							?>
						</p>
					</div>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

// 関連キーワード
$keywords = get_the_terms( get_the_ID(), 'nouns' );
?>
<?php if ( $keywords && ! is_wp_error( $keywords ) ) : ?>
	<h2 class="amp-hametuha-title">関連キーワード</h2>
	<div class="amp-hametuha-tag-list">
		<?php foreach ( $keywords as $keyword ) : ?>
			<a class="amp-hametuha-tag-link"
			   href="<?php echo get_term_link( $keyword ); ?>"><?php echo esc_html( $keyword->name ); ?></a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
