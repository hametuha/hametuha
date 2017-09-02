<ul class="nav nav-pills nav-justified nav-sub news-nav">
	<?php foreach ( get_terms( 'genre', [ 'parent' => 0 ] ) as $term ) : ?>
		<li>
			<a href="<?= get_term_link( $term ) ?>"><?= esc_html( $term->name ) ?></a>
		</li>
	<?php endforeach; ?>
</ul>
