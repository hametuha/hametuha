<?php

namespace Hametuha\Admin\MetaBox;


class SeriesPreview extends SeriesBase
{

	protected $context = 'side';

	protected $priority = 'high';

	protected $title = 'ePubプレビュー';

	public function doMetaBox( \WP_Post $post, array $screen ) {
		?>
		<p class="description">
			申請前に必ずプレビューを行ってください。
		</p>
		<ol>
			<li><a href="<?= home_url("epub/preview/cover/{$post->ID}", 'https') ?>" target="epub-preview">表紙</a></li>
			<li><a href="<?= home_url("epub/preview/toc/{$post->ID}", 'https') ?>" target="epub-preview">目次</a></li>
			<li><a href="<?= home_url("epub/preview/preface/{$post->ID}", 'https') ?>" target="epub-preview">序文</a></li>
			<li><a href="<?= home_url("epub/preview/afterword/{$post->ID}", 'https') ?>" target="epub-preview">あとがき</a></li>
			<li><a href="<?= home_url("epub/preview/creators/{$post->ID}", 'https') ?>" target="epub-preview">著者一覧</a></li>
			<li><a href="<?= home_url("epub/preview/colophon/{$post->ID}", 'https') ?>" target="epub-preview">奥付</a></li>
		</ol>
		<hr />
		<?php
		$sub_query = new \WP_Query([
			'post_type' => 'post',
			'post_parent' => $post->ID,
			'posts_per_page' => -1,
			'orderby' => [
				'menu_order' => 'DESC',
				'post_date' => 'ASC',
			]
		]);
		if( $sub_query->have_posts() ){
			$_old_post = $post;
			$endpoint = home_url("epub/preview/content/{$post->ID}", 'https');
			echo <<<HTML
					<select id="epub-previewer" data-endpoint="{$endpoint}">
						<option value="">本文をプレビュー</option>
HTML;
			while( $sub_query->have_posts() ){
				$sub_query->the_post();
				?>
				<option value="<?php the_ID() ?>"><?php the_title() ?></option>
			<?php
			}
			echo <<<HTML
					</select>
HTML;
			setup_postdata($_old_post);
			$post = $_old_post;
			$GLOBALS['post'] = $_old_post;
			wp_reset_postdata();
		}
	}
}
