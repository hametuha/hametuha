<?php get_header(); ?>

<div class="sub-header faq-header hidden-sm">
	<div class="container">

		<div class="row sub-header-box">
			<div class="col col-xs-12 col-sm-8">
				<h1 class="sub-header-title">ヘルプセンター</h1>
			</div>
			<div class="col col-xs-12 col-sm-4">
				<?php echo do_shortcode( '[hamelp-search label="知りたいことを入力してください"][/hamelp-search]' ); ?>
			</div>
		</div>


		<div class="row">
			<div class="col col-xs-12 col-sm-9">
				<ul class="nav nav-pills">
					<li class="<?php echo is_page( 'help' ) ? 'active' : ''; ?>">
						<a href="<?php echo home_url( 'help' ); ?>">ホーム</a>
					</li>
					<?php
					$terms = get_terms( 'faq_cat' );
					if ( $terms && ! is_wp_error( $terms ) ) :
						foreach ( $terms as $term ) :
							?>
						<li class="<?php echo is_tax( $term->taxonomy, $term->name ) || ( is_singular( 'faq' ) && has_term( $term->term_id, $term->taxonomy ) ) ? 'active' : ''; ?>">
							<a href="<?php echo get_term_link( $term ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</li>
							<?php
					endforeach;
endif;
					?>
				</ul>
			</div>
			<div class="col hidden-xs col-sm-3 text-right">
				<a class="btn btn-link" href="<?php echo home_url(); ?>"><i class="icon-exit"></i> 破滅派へ戻る</a>
			</div>
		</div>

	</div>
</div>

<?php if ( ! is_page() ) {
	get_header( 'breadcrumb' );
} ?>
