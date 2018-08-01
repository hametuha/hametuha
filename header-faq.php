<?php get_header() ?>

<div class="sub-header faq-header">
	<div class="container">

        <div class="row faq-header-box">
            <div class="col col-xs-12 col-sm-8">
                <h1 class="faq-header-title">ヘルプセンター</h1>
            </div>
            <div class="col col-xs-12 col-sm-4">
                <?php echo do_shortcode( '[hamelp-search label="知りたいことを入力してください"][/hamelp-search]' ) ?>
            </div>
        </div>


        <div class="row">
            <div class="col col-xs-12 col-sm-9">
                <ul class="nav nav-pills">
                    <li class="<?= is_page( 'help' ) ? 'active' : '' ?>">
                        <a href="<?= home_url( 'help' ) ?>">ホーム</a>
                    </li>
					<?php
                    $terms = get_terms( 'faq_cat' );
                    if ( $terms && ! is_wp_error( $terms ) ) :
					    foreach (  as $term ) : ?>
                        <li class="<?= is_tax( $term->taxonomy, $term->name ) || ( is_singular( 'faq' ) && has_term( $term->term_id, $term->taxonomy ) ) ? 'active' : '' ?>">
                            <a href="<?= get_term_link( $term ) ?>"><?= esc_html( $term->name ) ?></a>
                        </li>
					<?php endforeach; endif; ?>
                </ul>
            </div>
            <div class="col hidden-xs col-sm-3 text-right">
                <a class="btn btn-link" href="<?= home_url() ?>"><i class="icon-exit"></i> 破滅派へ戻る</a>
            </div>
        </div>

	</div>
</div>

<?php if ( ! is_page() ) {
    get_header( 'breadcrumb' );
} ?>
