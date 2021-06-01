<?php

namespace Hametuha\Admin\MetaBox;


class SeriesPreview extends SeriesBase {

	protected $context = 'side';

	protected $priority = 'high';

	protected $title = 'ePubプレビュー';

	public function doMetaBox( \WP_Post $post, array $screen ) {
		?>
		<p class="description">
			申請前に必ずプレビューを行ってください。
		</p>
		<ol id="series-additons-list">
			<li>
				<a href="<?php echo home_url( "epub/preview/titlepage/{$post->ID}", 'https' ); ?>" target="epub-preview">
					扉
				</a>
			</li>
			<li>
				<a href="<?php echo home_url( "epub/preview/toc/{$post->ID}", 'https' ); ?>" target="epub-preview">
					目次
				</a>
			</li>
			<li>
				<a href="<?php echo home_url( "epub/preview/foreword/{$post->ID}", 'https' ); ?>" target="epub-preview">
					序文
				</a>
			</li>
			<li>
				<a href="<?php echo home_url( "epub/preview/afterword/{$post->ID}", 'https' ); ?>" target="epub-preview">
					あとがき
				</a>
			</li>
			<li>
				<a href="<?php echo home_url( "epub/preview/contributors/{$post->ID}", 'https' ); ?>" target="epub-preview">
					著者一覧
				</a>
			</li>
			<li>
				<a href="<?php echo home_url( "epub/preview/colophon/{$post->ID}", 'https' ); ?>" target="epub-preview">
					奥付
				</a>
			</li>
			<li>
				<a href="<?php echo home_url( "epub/preview/ads/{$post->ID}", 'https' ); ?>" target="epub-preview">
					近刊書籍一覧
				</a>
			</li>
		</ol>
		<hr/>
		<?php
		$sub_query = new \WP_Query(
			[
				'post_type'      => 'post',
				'post_parent'    => $post->ID,
				'posts_per_page' => - 1,
				'orderby'        => [
					'menu_order' => 'DESC',
					'post_date'  => 'ASC',
				],
			]
		);
		if ( $sub_query->have_posts() ) {
			$_old_post = $post;
			$endpoint  = home_url( "epub/preview/content/{$post->ID}", 'https' );
			echo <<<HTML
					<select id="epub-previewer" data-endpoint="{$endpoint}">
						<option value="">本文をプレビュー</option>
HTML;
			while ( $sub_query->have_posts() ) {
				$sub_query->the_post();
				?>
				<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
				<?php
			}
			echo <<<HTML
					</select>
HTML;
			setup_postdata( $_old_post );
			$post            = $_old_post;
			$GLOBALS['post'] = $_old_post;
			wp_reset_postdata();
		}
		?>
		<hr />
		<h4>印刷確認</h4>
		<a class="button" href="<?php echo home_url( "/epub/print/{$post->ID}" ); ?>" target="_blank">
			まとめて印刷
		</a>
		<?php
	}
}
