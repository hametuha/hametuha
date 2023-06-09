<ul class="nav nav-pills">
	<li class="active"><a href="<?php echo get_post_type_archive_link( 'thread' ); ?>">破滅派掲示板トップ</a></li>
	<?php
	foreach ( get_terms( 'topic' ) as $topic ) {
		printf( '<li><a href="%s">%s</a></li>', get_term_link( $topic ), esc_html( $topic->name ) );
	}
	?>
</ul>
