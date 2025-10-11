<ul class="news-nav mt-2">
	<?php foreach ( get_terms( 'genre', [ 'parent' => 0 ] ) as $term ) : ?>
		<li class="news-nav-item">
			<a class="btn btn-block btn-outline-primary" href="<?php echo get_term_link( $term ); ?>"><?php echo esc_html( $term->name ); ?></a>
		</li>
	<?php endforeach; ?>
</ul>
