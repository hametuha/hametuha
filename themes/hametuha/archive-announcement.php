<?php
/**
 * 告知アーカイブ
 *
 */
get_header();
get_header( 'sub' );
?>

	<header class="book-list-header">
		<div class="container">
			<small>Announcement</small>
			<h1>
				<?php get_template_part( 'parts/h1' ); ?>
			</h1>
			<p class="description">
				<?php echo esc_html( get_post_type_object( 'announcement' )->description ); ?>
			</p>
			<p class="d-flex justify-content-start gap-3">
				<a class="btn btn-outline-primary" href="<?php echo esc_url( home_url( '/inquiry/' ) ); ?>">
					<?php esc_html_e( '掲載依頼をする', 'hametuha' ); ?>
				</a>
			</p>
		</div>
	</header>

	<?php get_header( 'breadcrumb' ); ?>

	<div class="container archive">
		<div class="row row-offcanvas row-offcanvas-right">
			<div class="col-12 col-md-9 main-container">
				<?php
				if ( have_posts() ) :
					?>
					<ol class="archive-container media-list">
						<?php
						$counter = 0;
						while ( have_posts() ) {
							the_post();
							$counter ++;
							$even = ( 0 === $counter % 2 ) ? ' even' : ' odd';
							get_template_part( 'parts/loop', get_post_type() );
						}
						?>
					</ol>
					<?php wp_pagenavi(); ?>
				<?php
				else :
					get_template_part( 'parts/no-content' );
				endif;
				?>

				<?php get_search_form(); ?>
			</div>

			<!-- //.main-container -->

			<?php get_sidebar(); ?>

		</div>
		<!-- // .offcanvas -->

	</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
