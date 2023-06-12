<?php get_header(); ?>
<?php get_header( 'sub' ); ?>
<?php get_header( 'breadcrumb' ); ?>

	<div class="container archive">

		<div class="row row-offcanvas row-offcanvas-right">

			<main class="col-xs-12 main-container">

				<header class="page-heading page-heading-text">
					<h1 class="page-heading-title">
						<?php echo ranking_title(); ?>
					</h1>

					<?php if ( ! is_ranking( 'top' ) ) : ?>
					<p class="page-heading-desc">
						<?php
						if ( ! is_ranking( 'top' ) ) {
							if ( is_ranking( 'best' ) ) {
								esc_html_e( '歴代ベストは2008年から現在までのものを合算したものです。', 'hametuha' );
							} elseif ( is_fixed_ranking() ) {
								printf( esc_html__( '【確定済み】%d件の投稿が対象です。', 'hametuha' ), loop_count() );
							} else {
								esc_html_e( 'このランキングは現在集計中です。順位は変動する可能性があります。', 'hametuha' );
							}
						}
						?>
						<a href="#ranking-detail"><?php esc_html_e( '（※ランキングの仕組み）', 'hametuha' ); ?></a>
					</p>
					<?php endif; ?>
				</header>

				<?php get_template_part( 'parts/ranking-archive', get_query_var( 'ranking' ) ); ?>

				<?php if ( ! is_ranking( 'top' ) ) : ?>

					<p class="ranking-back-to-top text-center">
						<a class="btn btn-lg btn-default" href="<?php echo home_url( '/ranking/' ); ?>">ランキングトップへ</a>
					</p>

				<?php endif; ?>

				<?php get_template_part( 'parts/ranking', 'calendar' ); ?>

				<div id="ranking-detail">

					<div class="panel panel-default">
						<div class="panel-heading">
							<h2 class="panel-title">ランキングの仕組み</h2>
						</div>
						<div class="panel-body">

							<h3>基本原則</h3>
							<ul>
								<li>ランキングは任意の期間でページビュー（以下PV）が多い順に決定されます。</li>
								<li>PVとは、そのページが表示された回数です。これにより「その作品が読まれた回数」の数を擬似的に表現しています。</li>
								<li>この基本原則は変わることがあります。</li>
							</ul>

							<h3>データ収集の仕組み</h3>
							<ul>
								<li>Google Analyticsという計測ツールを利用し、誰かが作品ページを開いたときにPVを取得してします。</li>
								<li>現在はPVであるため、同じ人が何回も同じページを開いたときもカウントされます。<small>（※今後は改善する予定です）</small>
								</li>
								<li>毎日深夜に前日のPVを記録し、集計用データとして保存します。</li>
								<li>集計中のランキングには「現在集計中」と表示されます。確定したランキングには「確定」と表示されます。</li>
							</ul>

						</div>
					</div>
				</div>

			</main>
			<!-- //.main-container -->

		</div>
		<!-- // .offcanvas -->

	</div><!-- //.container -->

<?php
get_sidebar( 'related' );
get_footer( 'books' );
get_footer();
