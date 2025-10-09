<?php
/**
 * 安否情報のテンプレート
 *
 */

get_header();
get_header( 'sub' );
?>
<header class="book-list-header">
	<div class="container">
		<small>Idea Notes</small>
		<h1>
			<?php
			$titles = [ __( '安否情報' ) ];
			if ( is_tax( 'anpi_cat' ) ) {
				$titles[] = esc_html( get_queried_object()->name );
			}
			echo esc_html( implode( ' 👉 ', $titles ) );
			?>

		</h1>
		<p class="description">
			<?php
			$desc = get_post_type_object( 'anpi' )->description;
			if ( is_tax( 'anpi_cat' ) ) {
				$desc = get_queried_object()->description ?: $desc;
			}
			echo esc_html( $desc );
			?>
		</p>
		<?php if ( current_user_can( 'read' ) ) : ?>
		<p>
			<button class="btn btn-lg btn-primary anpi-new">
				<?php esc_html_e( '安否報告する', 'hametuha' ); ?>
			</button>
		</p>
		<?php
		$terms = get_terms( [ 'taonomy' => 'anpi_cat' ] );
		if ( $terms && ! is_wp_error( $terms ) ) :
			?>
			<p>
			<?php foreach ( $terms as $term ) :
				printf(
					'<a href="%s" class="btn btn-outline-primary" style="margin-left: 1em;">%s</a>',
					esc_url( get_term_link( $term ) ),
					esc_html( $term->name )
				);
			endforeach; ?>
			</p>
		<?php endif; ?>

		<?php endif; ?>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<p class="text-muted mb-3 mt-3">
		<?php
		global $wp_query;
		printf( esc_html__( '%d件の安否情報が見つかりました', 'hametuha' ), $wp_query->found_posts );
		?>
	</p>

	<?php if ( have_posts() ) : ?>
		<div class="card-list row">

			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/loop', 'anpi' );
			endwhile;
			?>
		</div>

		<?php wp_pagenavi(); ?>
	<?php
	else :
		// 該当するコンテンツがない
		?>
		<div class="nocontents-found alert alert-warning mb-5">
			<p>
				<?php esc_html_e( '該当するアイデアは見つかりませんでした。アイデアの投稿・ストックなどをして、創作の種を集めておきましょう。', 'hametuha' ); ?>
			</p>
		</div>
		<?php
	endif;
	?>


</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
