<?php
/**
 * 連載一覧のテンプレート
 *
 * @feature-group series
 */

get_header();
get_header( 'sub' );
$series = \Hametuha\Model\Series::get_instance();
global $wp_query;
?>
<header class="book-list-header">
	<div class="container">
		<small>Series</small>
		<h1>連載・作品集</h1>
		<p class="description">
			<?php echo esc_html( $series->post_type->description ); ?>
		</p>
		<p class="d-flex justify-content-start gap-3">
			<?php
			foreach ( [
				[ __( '完結済み', 'hametuha' ), home_url( 'series/finished' ), (bool) get_query_var( 'is_finished' ) ],
				[ __( '電子書籍', 'hametuha' ), home_url( 'kdp' ), false ],
				[ __( 'すべて', 'hametuha' ), get_post_type_archive_link( 'series' ), ! get_query_var( 'is_finished' ) ],
			] as list( $label, $link, $is_active ) ) :
				$class = implode( ' ', [ 'btn', ( $is_active ? 'btn-primary' : 'btn-outline-primary' ) ] );
				?>
				<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $link ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</p>
		<p>
			<?php
			global $wp_query;
			printf( __( '%s作が登録されています。', 'hametuha' ), number_format( $wp_query->found_posts ) );
			?>
		</p>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<div class="row row-offcanvas row-offcanvas-right">

		<div class="col-xs-12 main-container">


			<?php  if ( have_posts() ) : ?>

				<ul class="list-book">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'parts/loop', 'series' );
					}
					?>
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
