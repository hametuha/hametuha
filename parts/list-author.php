<?php

$authors_recent = new WP_Query( [
	'author'         => get_the_author_meta( 'ID' ),
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'posts__not_in'  => get_the_ID(),
	'meta_key'       => '_current_pv',
	'orderby'        => [
		'meta_value_num' => 'DESC',
	],
] );
if ( $authors_recent->have_posts() ) :
	?>
	<div class="row row--recommend">
		<h3 class="recommend__header">
			<i class="icon-trophy-star"></i><br/>
			この作者の人気作<br/>
			<small>横にスクロールできるよ</small>
		</h3>
		<ul class="recommend__list">
			<?php while ( $authors_recent->have_posts() ) : $authors_recent->the_post(); ?>
				<?php get_template_part( 'parts/loop', 'recommend' ) ?>
			<?php endwhile; ?>
		</ul>
	</div>
	<?php wp_reset_postdata(); endif; ?>
