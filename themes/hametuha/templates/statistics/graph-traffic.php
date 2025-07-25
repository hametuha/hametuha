<?php
/**
 * @var array $endpoint
 */
?>

<div class="row">
	<div id="analytics-source" class="stat col-xs-12 col-sm-6" data-type="PieChart" data-endpoint="<?php echo $endpoint['source']; ?>">

		<h3><span class="icon-share2"></span> アクセス元</h3>

		<p class="text-muted">あなたの作品にどうやってたどり着いたかを表示しています。</p>

		<div class="stat__container" id="analytics-source-child"></div>

	</div>

	<div id="analytics-contributor" class="stat col-xs-12 col-sm-6" data-type="BarChart" data-endpoint="<?php echo $endpoint['contributor']; ?>">

		<h3><i class="icon-heart"></i> 貢献した人</h3>

		<p class="text-muted">あなたの作品にアクセスをもたらしてくれた人です。まずは「破滅派自動」がトップでなくなることを目指しましょう。</p>

		<div class="stat__container" id="analytics-contributor-child"></div>

	</div>

</div><!-- //.row -->

<hr />

<div class="row">
	<div id="analytics-query" class="stat col-xs-12" data-type="Table" data-endpoint="<?php echo $endpoint['keyword']; ?>">
		<h3><i class="icon-key"></i> 検索キーワード</h3>
		<p class="text-muted">あなたの作品にたどり着いた検索キーワードです。</p>
		<div class="stat__container" id="analytics-query-child"></div>
	</div>
</div><!-- //.row -->
