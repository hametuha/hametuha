<?php
/**
 * 連載・作品集の収録作一覧
 *
 * @feature-group series
 * @var array $args {
 *     @type WP_Query $query 子投稿のクエリ。
 * }
 */

$query = $args['query'] ?? null;
if ( ! $query instanceof WP_Query ) {
	return;
}
?>
<div class="series__row series__row--children" id="series-children">

	<div class="container series__inner">

		<div class="row">
			<div class="col-12 col-sm-4">
				<h2 class="series__title--list">
					<small class="series__title--caption">Works</small>
					収録作一覧
				</h2>
			</div>

			<div class="col-12 col-sm-8">
				<?php if ( $query->have_posts() ) : ?>
					<ol class="series__list">
						<?php
						$counter = 0;
						while ( $query->have_posts() ) {
							++$counter;
							$query->the_post();
							hameplate( 'parts/loop-series', get_post_type(), [
								'counter' => $counter,
							] );
						}
						wp_reset_postdata();
						?>
					</ol>

				<?php else : ?>

					<div class="alert alert-warning">
						<p>まだ作品が登録されていません。<a class="alert-link" href="#series-notification">破滅派をフォロー</a>して、作者の活躍に期待してください。
						</p>
					</div>

					<?php
				endif;
				wp_reset_postdata();
				?>
			</div>
		</div>


	</div>
	<!-- //.container -->

</div>
<!-- series_row--children -->
