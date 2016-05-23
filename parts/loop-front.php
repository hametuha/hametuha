<li>
	<a href="<?php the_permalink() ?>" class="clearfix">

		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'pinky' ) ?>
		<?php endif; ?>

		<h3 class="list-heading">
			<?= is_doujin_profile_page() ? get_the_title() : hametuha_censor( get_the_title() ) ?>
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
		</div>

		<?php if ( has_excerpt() ) : ?>
			<div class="list-excerpt">
				<?php $excerpt = trim_long_sentence( get_the_excerpt(), 98 ); ?>
				<?= esc_html( is_doujin_profile_page() ? $excerpt : hametuha_censor( $excerpt ) ); ?>
			</div>
		<?php endif; ?>

	</a>
</li>
