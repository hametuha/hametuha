<?php
/**
 * Template Name: 著者一覧テンプレート
 *
 * @since 8.4.6
 */

get_header();
get_header( 'sub' );
$authors = \Hametuha\Model\Author::get_instance();
global $wp_query;
the_post();
// プロ作家
$professionals = new WP_User_Query( [
	'count_total' => false,
	'role__in'    => [ 'author' ],
	'orderby'     => 'user_registered',
	'order'       => 'DESC',
	'meta_query'  => [
		[
			'key'     => 'flag_professional',
			'value'   => 1,
			'compare' => '=',
		],
		[
			'key'     => 'work_count',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		],
	],
] );
// 最近入った
$newbiews = new WP_User_Query( [
	'count_total' => false,
	'role__in'    => [ 'author' ],
	'orderby'     => 'user_registered',
	'order'       => 'DESC',
	'number'      => 8,
	'meta_query'  => [
		'relation' => 'AND',
		[
			'relation' =>'OR',
			[
				'key'     => 'flag_spam',
				'value'   => '0',
				'compare' => '=',
			],
			[
				'key'     => 'flag_spam',
				'compare' => 'NOT EXISTS',
			],

		],
		[
			'key'     => 'work_count',
			'value'   => 0,
			'compare' => '>=',
			'type'    => 'NUMERIC',
		],
	],
] );
// 編集部
$editors = new WP_User_Query( [
	'count_total' => false,
	'role__in'    => [ 'administrator', 'editor' ],
	'orderby'     => 'user_registered',
	'order'       => 'ASC',
] );
?>
<header class="book-list-header">
	<div class="container">
		<small>Authors</small>
		<h1><?php esc_html_e( '破滅派の執筆者', 'hametuha' ); ?></h1>
		<?php if ( has_excerpt() ) : ?>
		<div class="description">
			<?php the_excerpt(); ?>
		</div>
		<?php endif; ?>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<div class="row row-offcanvas row-offcanvas-right">

		<div class="col-xs-12 main-container">

			<?php
			// プロ作家
			if ( $professionals->get_results() ) :
				?>
				<section class="author-group">
					<h2 class="author-group-header"><?php esc_html_e( 'プロ作家', 'hametuha' ); ?></h2>
					<ul class="author-group-list">
						<?php
						foreach( $professionals->get_results() as $author ) {
							get_template_part( 'templates/doujin/loop', '', [ 'author' => $author ] );
						}
						?>
					</ul>
				</section>
			<?php endif; ?>

			<?php
			// 新人
			if ( $newbiews->get_results() ) :
				?>
				<section class="author-group">
					<h2 class="author-group-header"><?php esc_html_e( '新人さん', 'hametuha' ); ?></h2>
					<ul class="author-group-list">
						<?php
						foreach( $newbiews->get_results() as $author ) {
							get_template_part( 'templates/doujin/loop', '', [ 'author' => $author ] );
						}
						?>
					</ul>

					<p class="search-author-link text-center">
						<a class="btn btn-lg btn-primary" href="<?php echo home_url( '/authors/search/' ); ?>"><?php esc_html_e( 'すべての執筆者', 'hametuha' ); ?></a>
					</p>
				</section>
			<?php endif; ?>

			<?php
			// 編集部
			if ( $editors->get_results() ) :
				?>
			<section class="author-group">
				<h2 class="author-group-header"><?php esc_html_e( '破滅派編集部の面々', 'hametuha' ); ?></h2>
				<ul class="author-group-list">
					<?php
					foreach( $editors->get_results() as $author ) {
						get_template_part( 'templates/doujin/loop', '', [ 'author' => $author ] );
					}
					?>
				</ul>
			</section>
			<?php endif; ?>

			<?php
			// 検索
			get_template_part( 'searchform-author' );
			?>

			<main <?php post_class( [ 'post-content', 'post-blocks' ] ); ?>
				itemscope
				itemtype="http://schema.org/BlogPosting"
			>
				<?php the_content(); ?>
			</main>



		</div>
		<!-- //.main-container -->

	</div>
	<!-- // .offcanvas -->

</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
