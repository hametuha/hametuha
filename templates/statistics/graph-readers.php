<?php
/* @var array $endpoint */
?>

<div class="row">

	<div id="analytics-users" class="stat col-xs-12 col-sm-6" data-type="PieChart" data-endpoint="<?= $endpoint['users'] ?>">

		<h3><span class="icon-users"></span> あなたの作品の読者層</h3>

		<p class="text-muted"><span class="male">■</span>が男性、<span class="female">■</span>が女性です。色が暗いほど高齢です。</p>

		<div class="stat__container" id="analytics-users-child"></div>

	</div>

	<div id="analytics-region" class="stat col-xs-12 col-sm-6" data-type="GeoChart" data-endpoint="<?= $endpoint['region'] ?>">
		<h3><i class="icon-map"></i> 読者の地域</h3>
		<p class="text-muted">日本国内のアクセスだけカウントしています。</p>
		<div class="stat__container" id="analytics-region-child"></div>
	</div>

</div>

