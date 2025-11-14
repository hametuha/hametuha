<?php
/**
 * スレッドのヘッダー
 *
 * @feature-group thread
 */
get_header();
?>

<div class="sub-header thread-header">
	<div class="container">

		<div class="row sub-header-box mb-3">
			<div class="col col-12 col-md-8">
				<h1 class="sub-header-title">破滅派掲示板</h1>
			</div>
			<div class="col col-12 col-md-4">
				<form action="<?php echo get_post_type_archive_link( 'thread' ); ?>" class="d-flex">
					<div class="input-group">
						<input type="search" class="form-control" name="s" placeholder="掲示板を検索" value="<?php the_search_query(); ?>" />
						<button class="btn btn-secondary" type="submit">検索</button>
					</div>
				</form>
			</div>
		</div>


		<div class="row">
			<div class="col col-12 col-sm-9">
				<ul class="nav nav-pills">
					<li class="nav-item">
						<a class="nav-link btn btn-outline-light<?php echo is_post_type_archive( 'thread' ) ? ' active' : ''; ?>" href="<?php echo get_post_type_archive_link( 'thread' ); ?>">ホーム</a>
					</li>
					<?php
					$terms = get_terms( 'topic' );
					if ( $terms && ! is_wp_error( $terms ) ) :
						foreach ( $terms as $term ) :
							$is_active = is_tax( $term->taxonomy, $term->name ) || ( is_singular( 'thread' ) && has_term( $term->term_id, $term->taxonomy ) );
							?>
						<li class="nav-item">
							<a class="nav-link btn btn-outline-light<?php echo $is_active ? ' active' : ''; ?>" href="<?php echo get_term_link( $term ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</li>
							<?php
					endforeach;
endif;
					?>
				</ul>
			</div>
			<div class="col d-none d-sm-block col-sm-3 text-end">
				<a class="btn btn-link" href="<?php echo home_url(); ?>"><i class="icon-exit"></i> 破滅派へ戻る</a>
			</div>
		</div>

	</div>
</div>

<?php
if ( ! is_page() ) {
	echo '<div class="mt-3 mb-3">';
	get_header( 'breadcrumb' );
	echo '</div>';
} ?>
