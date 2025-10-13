<?php
/**
 * FAQ用ヘッダー
 *
 * @feature-group faq
 */
get_header(); ?>

<div class="sub-header faq-header">
	<div class="container">

		<div class="row sub-header-box">
			<div class="col col-12 col-md-8">
				<h1 class="sub-header-title">ヘルプセンター</h1>
			</div>
			<div class="col col-12 col-md-4">
				<?php echo do_shortcode( '[hamelp-search label="知りたいことを入力してください"][/hamelp-search]' ); ?>
			</div>
		</div>


		<div class="row">
			<div class="col col-12 col-sm-9">
				<ul class="nav nav-pills">
					<li class="nav-item">
						<a class="nav-link btn btn-outline-light<?php echo is_page( 'help' ) ? ' active' : ''; ?>" href="<?php echo home_url( 'help' ); ?>">ホーム</a>
					</li>
					<?php
					$terms = get_terms( 'faq_cat' );
					if ( $terms && ! is_wp_error( $terms ) ) :
						foreach ( $terms as $term ) :
							$is_active = is_tax( $term->taxonomy, $term->name ) || ( is_singular( 'faq' ) && has_term( $term->term_id, $term->taxonomy ) );
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
