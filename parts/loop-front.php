<?php
$title = get_the_title();
$title_display = hametuha_censor( $title );
$excerpt = trim_long_sentence( get_the_excerpt(), 98 );
$excerpt_display = hametuha_censor( $excerpt );
$censored = ! is_doujin_profile_page() && ( ( $title != $title_display ) || ( $excerpt != $excerpt_display ) );
$no_desc = isset( $description ) && ( false === $description );
?>
<li>
	<a href="<?php the_permalink() ?>" class="clearfix">

		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'pinky' ) ?>
		<?php endif; ?>

		<h3 class="list-heading">
			<?= is_doujin_profile_page() ? $title : $title_display ?>
			<?php foreach ( get_the_category() as $cat ) : ?>
				<small>
					<?= esc_html( $cat->name ) ?>
				</small>
			<?php endforeach; ?>
			<?php if ( 'series' == get_post_type() ) : ?>
				<span class="badge"><?= number_format( $post->children ) ?></span>
			<?php endif; ?>
		</h3>

		<div class="list-meta">
			<?= get_avatar( get_the_author_meta( 'ID' ), 60 ) ?>
			<?php the_author() ?>
			<?php the_date() ?>
			<?php if ( is_new_post( 3 ) ) : ?>
				<span class="label label-danger">New</span>
			<?php endif; ?>
			<?php if ( $censored ) : ?>
				<span class="label label-danger censored">検閲済み</span>
			<?php endif; ?>
		</div>

		<?php if ( has_excerpt() && ! $no_desc ) : ?>
			<div class="list-excerpt">
				<?= esc_html( is_doujin_profile_page() ? $excerpt : $excerpt_display ); ?>
			</div>
		<?php endif; ?>

	</a>
</li>
