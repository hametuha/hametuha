<?php

$authors_ranking = hametuha_get_author_popular_works( null, 3 );
$authors_recnent = new WP_Query( [
	'author'         => get_the_author_meta( 'ID' ),
	'posts_per_page' => 3,
	'post_type'      => 'post',
	'posts__not_in'  => get_the_ID(),
	'orderby'        => [
		'date' => 'DESC',
	],
] );
	?>
<section class="m20">

	<div class="row">

		<div class="col-xs-12 col-sm-6">

			<h2 class="list-title">この作者の人気作</h2>
            <?php if ( $authors_ranking ) : ?>
                <ul class="post-list no-excerpt">
					<?php foreach ( $authors_ranking as $post ) : setup_postdata( $post ); ?>
					<?php get_template_part( 'parts/loop', 'front' ) ?>
                    <?php endforeach; wp_reset_postdata(); ?>
                </ul>
			<?php else : ?>
                <div class="alert alert-light text-center">
                    今後の活躍にご期待ください。
                </div>
            <?php endif ; ?>
		</div>

		<div class="col-xs-12 col-sm-6">
			<h2 class="list-title">この作者の最新作</h2>
			<?php if ( $authors_recnent->have_posts() ) : ?>
				<ul class="post-list no-excerpt">
					<?php while ( $authors_recnent->have_posts() ) : $authors_recnent->the_post(); ?>
						<?php get_template_part( 'parts/loop', 'front' ) ?>
					<?php endwhile; ?>
				</ul>
			<?php else : ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-8 col-xs-offset-2">
			<a href="<?= home_url( '/doujin/detail/'.get_the_author_meta( 'nicename' ).'/' ) ?>" class="btn btn-default btn-lg btn-block">もっと見る</a>
		</div>
	</div>

</section>
<?php wp_reset_postdata(); ?>
