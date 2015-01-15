<?php

/** @var \Hametuha\Admin\Screens\Stats $this */

?>

<div class="updated">
	<p>
		このページでは、最近一ヶ月<strong>（2014年11月移行から計測開始）</strong>のアクセス情報を表示しています。
		投稿を削除した場合、それらのアクセス統計も削除されているので、正確な値は出なくなります。
		わからないことがあったら、<a href="<?= home_url('thread/機能要望/') ?>" target="_blank">スレッド</a>で質問してください。
	</p>
</div>

<div class="analytics-wrap">

	<div class="analytics-date-changer">
		<form id="analytics-date-form" method="get" action="<?= admin_url('admin-ajax.php') ?>">
			<label>期間: <input type="text" class="datepicker" name="from" value="<?= date_i18n('Y-m-d', strtotime('1 month ago', current_time('timestamp'))) ?>" />から</label>
			<label><input type="text" class="datepicker" name="to" value="<?= date_i18n('Y-m-d') ?>" />まで</label>
			<input type="submit" class="button-primary" value="再表示" />
		</form>
	</div>

	<div id="analytics-pv" class="stat loading" data-type="Line" data-action="<?= HametuhaUserPvs::ACTION ?>" data-nonce="<?= HametuhaUserPvs::get_nonce() ?>">
		<h3><span class="dashicons dashicons-chart-line"></span> あなたの作品の閲覧数</h3>
		<p class="description">指定期間内の全作品総閲覧数を表示しています。</p>
		<canvas width="500" height="250"></canvas>
		<p class="error">
			データがありません。
		</p>
		<span class="dashicons dashicons-update"></span>
	</div>

	<hr />

	<div id="analytics-popular" class="stat col2 loading gap" data-type="Bar" data-action="<?= HametuhaPopularPosts::ACTION ?>" data-nonce="<?= HametuhaPopularPosts::get_nonce() ?>">
		<h3><span class="dashicons dashicons-star-filled"></span> 人気の作品</h3>
		<p class="description">あなたの作品のうち、上位10件を表示しています。</p>
		<canvas width="250" height="250"></canvas>
		<p class="error">
			データがありません。
		</p>
		<span class="dashicons dashicons-update"></span>
	</div>

	<div id="analytics-users" class="stat col2 loading" data-type="Pie" data-action="<?= HametuhaReaderSegment::ACTION ?>" data-nonce="<?= HametuhaReaderSegment::get_nonce() ?>">
		<h3><span class="dashicons dashicons-id-alt"></span> あなたの作品の読者</h3>
		<p class="description"><span class="male">■</span>が男性、<span class="female">■</span>が女性です。色が褪せているほど高齢です。</p>
		<canvas width="250" height="250"></canvas>
		<p class="error">
			データがありません。
		</p>
		<span class="dashicons dashicons-update"></span>
	</div>

	<hr style="clear: both;" />

</div>

