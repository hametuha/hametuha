<?php

namespace Hametuha\Admin\MetaBox;


/**
 * Series list
 *
 * @package Hametuha\Admin\MetaBox
 */
class SeriesList extends SeriesBase
{

	protected $context = 'normal';

	protected $priority = 'high';

	protected $title = '登録されている作品';

	public function doMetaBox( \WP_Post $post, array $screen ) {
		$_old_post = $post;
		$users = get_series_authors($post);
		$editor = new \WP_User($post->post_author);
		$series_query = new \WP_Query(   [
			'post_type' => 'post',
			'post_parent' => $post->ID,
			'posts_per_page' => -1,
			'orderby' => [
				'menu_order' => 'DESC',
				'post_date' => 'ASC',
			]
		]);
		if( $series_query->have_posts() ){
			?>
			<p class="description">現在登録されているシリーズです。投稿は日付の遅い順に並びます。</p>
			<ol>
				<?php while( $series_query->have_posts() ): $series_query->the_post(); ?>
					<li><a href="<?php the_permalink() ?>" target="epub-preview"><?php the_title() ?></a></li>
				<?php endwhile; setup_postdata($_old_post); wp_reset_postdata(); ?>
			</ol>
			<hr />
			<p>
				<strong>著者　：</strong> <?= implode(', ', array_map(function( \WP_User $user ){
					return sprintf('<a href="%s">%s</a>', esc_url(get_author_posts_url($user->ID, $user->user_nicename)), $user->display_name);
				}, $users)) ?><br />
				<strong>編集者：</strong> <?= esc_html($editor->display_name) ?>
			</p>
		<?php
		}else{
			echo <<<HTML
				<p class="description">まだ投稿が追加されていません。</p>
HTML;
		}
	}


}
