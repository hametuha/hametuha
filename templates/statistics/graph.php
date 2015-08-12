<?php
/** @var array $endpoint */
?>
<div id="analytics-pv" class="stat" data-type="ComboChart" data-endpoint="<?= $endpoint['access'] ?>">

	<h3><i class="icon-chart"></i> あなたの作品の閲覧数</h3>

	<p class="text-muted">指定期間内の全作品総閲覧数を表示しています。</p>

	<div class="stat__container" id="analytics-pv-child"></div>

</div><!-- .stat -->

<hr/>

<div id="analytics-popular" class="stat" data-type="Table" data-endpoint="<?= $endpoint['popular'] ?>">
	<h3><i class="icon-star"></i> 人気のページ</h3>

	<p class="text-muted">
		指定期間内でもっとも人気のあったページを表示しています。
	</p>

	<div class="stat__container" id="analytics-popular-child"></div>

</div>