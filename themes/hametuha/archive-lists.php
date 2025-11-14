<?php
/**
 * リスト一覧のテンプレート
 *
 * @feature-group list
 * @since 7.9.0
 */

get_header();
get_header( 'sub' );
if ( current_user_can( 'read' ) ) {
	// ユーザーがログインしてたらリスト用のフォームを追加
	wp_enqueue_script( 'hametuha-components-list' );
}
global $wp_query;
?>
<header class="book-list-header">
	<div class="container">
		<small>Anthology</small>
		<h1>
		<?php
			$author_name = get_query_var( 'author_name' );
			$is_mine     = get_query_var( 'my-content' );
		if ( $author_name ) {
			$author = get_user_by( 'slug', $author_name );
			if ( $author ) {
				$author_name = $author->display_name;
			}
			printf( __( '%sによる選書', 'hametuha' ), esc_html( $author_name ) );
		} elseif ( 'lists' === $is_mine ) {
			esc_html_e( 'あなたの選書', 'hametuha' );
		} else {
			esc_html_e( '破滅派選書', 'hametuha' );
		}
		?>
		</h1>
		<p class="description">
			<?php echo esc_html( get_post_type_object( 'lists' )->description ); ?>
		</p>
		<p>
			<?php if ( current_user_can( 'read' ) ) : ?>
				<button class="btn btn-lg btn-primary list-creator" title="リストを追加">
					<?php esc_html_e( 'リストを作成する', 'hametuha' ); ?>
				</button>
			<?php endif; ?>
			<?php
			switch ( $is_mine ) {
				case 'lists':
					$list_query = 'your';
					break;
				case 'recommends':
					$list_query = 'recommend';
					break;
				default:
					$list_query = 'all';
					break;
			}
			$list_pages = [
				[ __( 'すべてのリスト', 'hametuha' ), get_post_type_archive_link( 'lists' ), 'all' ],
				[ __( 'おすすめ', 'hametuha' ), home_url( 'recommends/' ), 'recommend' ],
			];
			if ( current_user_can( 'read' ) ) {
				$list_pages[] = [ __( 'あなたのリスト', 'hametuha' ), home_url( 'your/lists/' ), 'your' ];
			}
			foreach ( $list_pages as list( $label, $url, $current_query ) ) {
				$active    = $current_query === $list_query;
				$classes   = [ 'btn', 'btn-lg' ];
				$classes[] = $active ? 'btn-secondary' : 'btn-outline-secondary';
				printf(
					'<a href="%s" class="%s" style="margin-right: 0.5em">%s</a>',
					esc_url( $url ),
					esc_attr( implode( ' ', $classes ) ),
					esc_html( $label )
				);
			}
			?>
		</p>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<p class="text-muted mb-3 mt-3">
		<?php
		global $wp_query;
		printf( esc_html__( '%d件のリストが見つかりました', 'hametuha' ), $wp_query->found_posts );
		?>
	</p>

	<?php if ( have_posts() ) : ?>
		<div class="card-list row">

			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/loop', 'lists' );
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
				<?php esc_html_e( '該当するリストは見つかりませんでした。ぜひあなたのリストも作成してみてください。', 'hametuha' ); ?>
			</p>
		</div>
		<?php
	endif;

	// タグクラウドを出力
	get_search_form();
	?>


</div><!-- //.container -->

<?php
get_footer( 'ebooks' );
get_footer( 'books' );
get_footer();
