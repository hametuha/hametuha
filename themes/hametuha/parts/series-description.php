<?php
/**
 * 単巻書籍の内容紹介
 *
 * 本文が破滅派にないため収録作一覧を組み立てられない。書き手が入力した
 * 自由記述の HTML をそのまま表示する。空欄ならセクションごと出さない。
 *
 * @feature-group series
 * @var array $args {
 *     @type string $description 内容紹介のHTML。
 * }
 */

$description = $args['description'] ?? '';
if ( ! $description ) {
	return;
}
?>
<div class="series__row series__row--children series__row--description" id="series-children">

	<div class="container series__inner">

		<div class="row">
			<div class="col-12 col-sm-4">
				<h2 class="series__title--list">
					<small class="series__title--caption">About this Book</small>
					内容紹介
				</h2>
			</div>

			<div class="col-12 col-sm-8">
				<div class="series__description">
					<?php
					// 保存時は素通しなので、出力時に必ず kses を通す。将来この行に変換が
					// 挟まっても守られるよう、サニタイザを最後に置く。
					// wpautop はブロック要素を包まないので、HTMLで書いた場合はそのまま残る。
					echo wp_kses_post( wpautop( $description ) );
					?>
				</div>
			</div>
		</div>

	</div>
	<!-- //.container -->

</div>
<!-- //.series__row--description -->
