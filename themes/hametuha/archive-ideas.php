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
		<small>Idea Notes</small>
		<h1><?php esc_html_e( '破滅派アイデア帳', 'hametuha' ); ?></h1>
		<p class="description">
			<?php echo esc_html( get_post_type_object( 'ideas' )->description ); ?>
		</p>
		<p>
			<button class="btn btn-lg btn-primary">
				<?php esc_html_e( 'アイデアを投稿する', 'hametuha' ); ?>
			</button>
		</p>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<?php
	// TODO: ここにフィルターを書く
	// タグの絞り込み
	// 自分の、ストックしたもの、他人
	//
	?>


	<?php if ( have_posts() ) : ?>
		<div class="card-list row">

			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/loop', 'ideas' );
			endwhile;
			?>
		</div>

		<?php wp_pagenavi(); ?>
	<?php
	else :
		// 該当するコンテンツがない
		get_template_part( 'parts/no', 'content' );
	endif;
	?>

</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
