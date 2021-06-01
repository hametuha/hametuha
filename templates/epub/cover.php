<?php
/** @var WP_Post $series */
/** @var WP_Post $post*/
/** @var Hametuha\Rest\Epub $this */
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<section class="cover">

	<?php if ( has_post_thumbnail( $post->ID ) ) : ?>
		<!-- 表紙あり -->
		<h1 class="cover-image">
			<?php
			the_post_thumbnail(
				$this->series->image_size,
				[
					'alt'   => get_the_title( $post ),
					'title' => get_the_title( $post ),
				]
			)
			?>
		</h1>
	<?php else : ?>
		<!-- 表紙なし -->
		<h1 class="no-cover">
			<span><?php the_title(); ?></span>
			<?php if ( ( $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ) ) ) : ?>
				<small class="subtitle"><?php echo esc_html( $subtitle ); ?></small>
			<?php endif; ?>
			<small><?php echo esc_html( hametuha_author_name( $post ) ); ?></small>
			<small class="publisher-credit">破滅派</small>
		</h1>
	<?php endif; ?>

</section>

<?php get_template_part( 'templates/epub/footer' ); ?>
