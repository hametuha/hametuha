<?php
/**
 * コンテンツが存在しなかったときに表示されるテンプレート
 */
?>
<div class="nocontents-found alert alert-warning">
	<p>該当するコンテンツがありませんでした。以下の方法をお試しください。</p>
	<ul>
		<li>検索ワードを変えてみる</li>
		<li>カテゴリー、タグから探す</li>
		<li>検索ワードの数を減らして、絞り込み検索と組み合せる</li>
	</ul>
	<p>
		改善要望などありましたら、
		<a class="alert-link" href="<?php echo home_url( '/inquiry/' ); ?>">
			お問い合わせ
		</a>
		からお願いいたします。
	</p>
</div>
