<?php
/**
 * Template Name: キャンペーン一覧
 */
get_header();
?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container archive">

		<div class="row row-offcanvas row-offcanvas-right">

			<div class="col-xs-12 col-sm-9 main-container">

                <div class="archive-meta">
                    <h1>
						<?php get_template_part( 'parts/h1' ); ?>
                    </h1>
                    <div class="desc">
						<?php get_template_part( 'parts/meta-desc' ); ?>
                    </div>
                </div>

                <?php the_post(); ?>
                <div class="post-content clearfix" itemprop="articleBody">
					<?php the_content() ?>
                </div><!-- //.post-content -->

                <div class="widget-campaign-list">
					<?php foreach ( hametuha_recent_campaigns( 0, false ) as $campaign ) {
						hameplate( 'parts/loop', 'campaign', [
							'campaign' => $campaign
						] );
					} ?>
                </div>

			</div>
			<!-- //.main-container -->

			<?php get_sidebar() ?>

		</div>
		<!-- // .offcanvas -->

	</div><!-- //.container -->

<?php get_footer(); ?>