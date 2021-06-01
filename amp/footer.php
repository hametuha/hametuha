<footer class="amp-wp-footer">
	<div>
		<h2>
			<?php echo esc_html( $this->get( 'blog_name' ) ); ?>
			<small>破滅派のおとどけする文学ニュース</small>
		</h2>
	</div>
	<div class="amp-hametuha-category">
		<?php
		$terms = get_terms(
			[
				'taxonomy' => 'genre',
				'parent'   => 0,
			]
		);
		foreach ( $terms as $term ) {
			printf( '<a href="%s" class="amp-hametuha-category-link">%s</a>', get_term_link( $term ), esc_html( $term->name ) );
		}
		?>
	</div>
	<div>
		<p>
			<a href="<?php echo esc_url( esc_html__( 'https://wordpress.org/', 'amp' ) ); ?>"><?php echo esc_html( sprintf( __( 'Powered by %s', 'amp' ), 'WordPress' ) ); ?></a>
		</p>
		<a href="<?php echo get_post_type_archive_link( 'news' ); ?>" class="back-to-top">
			はめにゅーTOP
		</a>
	</div>
</footer>
