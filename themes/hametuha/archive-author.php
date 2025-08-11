<?php
/**
 * Template Name: 著者検索
 *
 * @since 8.4.6
 */

get_header();
get_header( 'sub' );
$authors = \Hametuha\Model\Author::get_instance();
global $wp_query;
?>
<header class="book-list-header">
	<div class="container">
		<small>Authors</small>
		<h1><?php esc_html_e( '破滅派の執筆者', 'hametuha' ); ?></h1>
		<div class="description">
			<?php esc_html_e( '破滅派で作品を公開している誉れ高き作家の皆さん', 'hametuha' ); ?>
		</div>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<div class="row row-offcanvas row-offcanvas-right">

		<div class="col-xs-12 main-container">

				<section class="author-group">
					<h2 class="author-group-header">
						<?php printf( esc_html__( '%d名の執筆者', 'hametuha' ), $wp_query->found_posts ); ?>
					</h2>
					<?php if ( have_posts() ) : ?>
					<ul class="author-group-list">
						<?php
						while ( have_posts() ) {
							the_post();
							$author = new WP_User( get_the_author_meta( 'ID' ) );
							get_template_part( 'templates/doujin/loop', '', [ 'author' => $author ] );
						}
						?>
					</ul>
					<?php wp_pagenavi( [ 'query' => $wp_query ] ); ?>

					<?php else: ?>
						<div class="alert alert-warning">
							<p>
								<?php esc_html_e( '該当する執筆者は見つかりませんでした。あらためて検索してみてください。', 'hametuha' ); ?>
							</p>
						</div>
					<?php endif; ?>
				</section>
			<?php
			// 検索
			get_template_part( 'searchform-author' );
			?>

		</div>
		<!-- //.main-container -->

	</div>
	<!-- // .offcanvas -->

</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
