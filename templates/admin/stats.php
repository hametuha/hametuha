<?php

/** @var \Hametuha\Admin\Screens\Stats $this */

?>

<div class="updated">

</div>

<div class="analytics-wrap">

	<div class="analytics-date-changer">
		<form id="analytics-date-form" method="get" action="<?= admin_url('admin-ajax.php') ?>">
			<label>期間: <input type="text" class="datepicker" name="from" value="<?= date_i18n('Y-m-d', strtotime('1 month ago', current_time('timestamp'))) ?>" />から</label>
			<label><input type="text" class="datepicker" name="to" value="<?= date_i18n('Y-m-d') ?>" />まで</label>
			<input type="submit" class="button-primary" value="再表示" />
		</form>
	</div>


	<hr />

	<div id="analytics-popular" class="stat col2 loading gap" data-type="BarChart" data-action="<?= HametuhaPopularPosts::ACTION ?>" data-nonce="<?= HametuhaPopularPosts::get_nonce() ?>">
		<h3><span class="dashicons dashicons-star-filled"></span> 人気の作品</h3>
		<p class="description">あなたの作品のうち、上位10件を表示しています。</p>
		<div id="analytics-popular-child"></div>
		<p class="error">
			データがありません。
		</p>
		<span class="dashicons dashicons-update"></span>
	</div>




	<hr style="clear: both;" />

</div>

