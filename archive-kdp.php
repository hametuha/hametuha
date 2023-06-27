<?php
/**
 * KDP本紹介様のテンプレート
 *
 * @since 7.9.0
 */

get_header();
get_header( 'sub' );
$series = \Hametuha\Model\Series::get_instance();
global $wp_query;
?>
<header class="book-list-header">
	<div class="container">
		<small>Hametuha on amazon</small>
		<h1>破滅派の電子書籍</h1>
		<p class="description">
			破滅派がリリースしている電子書籍<?php echo number_format( $wp_query->found_posts ); ?>冊はAmazonのKindleストアで入手できます。
		</p>
		<p>
			<a class="btn btn-trans btn-lg btn-amazon" href="https://amzn.to/3XqCRt0" target="_blank"
			   data-outbound="kdp"
			   data-action="search"
			   data-label=""
			   data-value="0">
				<i class="icon-amazon"></i>
				Amazonで見る
			</a>
		</p>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<div class="row row-offcanvas row-offcanvas-right">

		<div class="col-xs-12 main-container">


			<?php  if ( have_posts() ) : ?>

				<ul class="list-book">
					<?php while ( have_posts() ) : the_post(); ?>

						<li class="list-book-item">
							<a href="<?php the_permalink(); ?>" class="list-book-link">
								<figure class="list-book-cover">
									<?php the_post_thumbnail( 'medium', [ 'class' => 'list-book-image' ] ); ?>
								</figure>
							</a>

							<div class="list-book-body">
								<p class="list-book-text">
									<span class="list-book-title"><?php the_title(); ?></span>
									<span class="list-book-author"><?php the_author(); ?></span>
								</p>

								<p class="list-book-meta">
									&yen; <?php the_series_price(); ?>
								</p>

								<p class="list-book-action">
									<a href="<?php the_permalink(); ?>">詳細</a>
									<a href="<?php hametuha_the_kdp_url(); ?>" title="<?php esc_attr_e( 'Amazonで買う', 'hametuha' ) ?>"><i class="icon-amazon"></i></a>
								</p>

							</div>

						</li>
					<?php endwhile; ?>
				</ul>


				<?php wp_pagenavi(); ?>


				<?php
			else :
				get_template_part( 'parts/no', 'content' );
			endif;

			// Extras
			get_search_form();
			?>

		</div>
		<!-- //.main-container -->

	</div>
	<!-- // .offcanvas -->

</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
