<?php
/** @var \Hametuha\Rest\Sales $this */
/** @var array $endpoint */
?>
<div id="kdp-sales" class="stat" data-type="LineChart" data-endpoint="<?= $endpoint['sales'] ?>">

	<h3><i class="icon-cloud-download"></i> ダウンロード数および売り上げ</h3>

	<p class="text-muted">指定期間内の全作品総閲覧数を表示しています。</p>

	<div class="stat__container" id="kdp-sales-child"></div>

</div><!-- .stat -->

<hr/>

<div id="kdp-downloads" class="stat" data-type="Table" data-endpoint="<?= $endpoint['title'] ?>">
	<h3><i class="icon-books"></i> 作品別</h3>

	<p class="text-muted">
		上記データの作品別詳細です。
	</p>

	<div class="stat__container" id="kdp-downloads-child"></div>

</div><!-- //.stat -->
