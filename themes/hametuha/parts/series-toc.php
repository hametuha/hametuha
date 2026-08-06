<?php
/**
 * 単巻書籍の目次
 *
 * 本文が破滅派にないため収録作一覧を組み立てられない。書き手が入力した
 * 自由記述の HTML をそのまま表示する。空欄ならセクションごと出さない。
 *
 * @feature-group series
 * @var array $args {
 *     @type string $toc 目次のHTML。
 * }
 */

$toc = $args['toc'] ?? '';
if ( ! $toc ) {
	return;
}
?>
<div class="series__row series__row--children series__row--toc" id="series-children">

	<div class="container series__inner">

		<div class="row">
			<div class="col-12 col-sm-4">
				<h2 class="series__title--list">
					<small class="series__title--caption">Contents</small>
					目次
				</h2>
			</div>

			<div class="col-12 col-sm-8">
				<div class="series__toc">
					<?php echo wp_kses_post( $toc ); ?>
				</div>
			</div>
		</div>

	</div>
	<!-- //.container -->

</div>
<!-- //.series__row--toc -->
