<li class="recent-post-list">
	<a href="<?php the_permalink(); ?>">
		<h3><?php echo hametuha_censor( get_the_title() ); ?></h3>
		<p class="author">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 48 ); ?>
			<?php echo hametuha_censor( get_the_author() ); ?>
		</p>
		<p class="meta">
			<i class="icon-folder"></i>
			<?php
			switch ( get_post_type() ) {
				case 'post':
					$tax = get_the_category();
					break;
				case 'anpi':
				case 'faq':
					$tax = get_the_terms( $post, get_post_type() . '_cat' );
					break;
				case 'thread':
					$tax = get_the_terms( $post, 'topic' );
					break;
				default:
					$tax = false;
					break;
			}
			if ( $tax && ! is_wp_error( $tax ) ) :
				?>
				<?php
				echo implode( ', ', array_map( function ( $cat ) {
					return sprintf( '<span>%s</span>', esc_html( $cat->name ) );
				}, $tax ) )
				?>
			<?php endif; ?>
			<i class="icon-calendar"></i> <?php the_time( 'Y年n月j日' ); ?>
		</p>
	</a>

</li>
