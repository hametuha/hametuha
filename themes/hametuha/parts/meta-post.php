<?php
/**
 * 投稿一覧で表示するメタ情報
 *
 */

?>
<header class="post-archive-header mb-3">

	<h1>
		<?php get_template_part( 'parts/h1' ); ?>
	</h1>
	<small>
		<?php
		// ページ数を出力
		list( $cur_page, $total_page ) = hametuha_page_numbers();
		printf(
			'全%s作（%d/%dページ）',
			number_format_i18n( loop_count() ),
			$cur_page,
			$total_page
		);
		?>
	</small>

</header>

<div class="post-archive-desc mb-5">
	<?php get_template_part( 'parts/meta-desc' ); ?>
</div>

<?php if ( is_author() ) : ?>
	<?php get_template_part( 'parts/author' ); ?>
<?php endif; ?>
