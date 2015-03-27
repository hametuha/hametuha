<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

<header class="header header--colophon">
	<h1 class="title">
		書誌情報
	</h1>
	<div class="meta">
		<div class="date">
			<?php the_date() ?>発行<br />
			<?php the_modified_date() ?>更新
		</div>
	</div>
</header>

<article class="content content--colophon">
	<table class="colophon">
		<caption>書誌情報</caption>
		<tr>
			<th>初出</th>
			<td>
				<a class="url" href="<?php the_permalink() ?>"><?php the_permalink() ?></a>
			</td>
		</tr>
		<tr>
			<th>著者</th>
			<td>
				<?= implode(' / ', array_map(function( WP_User $author ){
					return esc_html($author->display_name);
				}, $authors)) ?>
			</td>
		</tr>
		<tr>
			<th>編集者</th>
			<td><?php the_author() ?></td>
		</tr>
		<tr>
			<th>発行人</th>
			<td>高橋文樹</td>
		</tr>
		<tr>
			<th>発行所</th>
			<td>
				<?php bloginfo('name') ?>
			</td>
		</tr>
		<?php foreach( [
			'asin' => 'ASIN',
		] as $key => $label): if( !($value = get_post_meta(get_the_ID(), $key, true)) ){ continue; } ?>
		<tr>
			<th><?= esc_html($label) ?></th>
			<td><?= esc_html($value) ?></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<table class="colophon">
		<caption>連絡先</caption>
		<tr>
			<th>名称</th>
			<td>株式会社破滅派</td>
		</tr>
		<tr>
			<th>電話</th>
			<td><a href="tel:05055328327">050-5532-8327</a></td>
		</tr>
		<tr>
			<th>メール</th>
			<td><a href="mailto:info@hametuha.co.jp">info@hametuha.co.jp</a></td>
		</tr>
		<tr>
			<th>住所</th>
			<td>〒262-0019 東京都港区南青山2-11-13 南青山ビル4F</td>
		</tr>
	</table>
</article>


<footer class="footer footer--colophon">
	&copy; <?php the_time('Y') ?> <?= implode(' / ', array_map(function( WP_User $author ){
		return esc_html($author->display_name);
	}, $authors)) ?> / Hametuha INC.
</footer>

<?php get_template_part('templates/epub/footer') ?>
