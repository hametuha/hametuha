<?php
/**
 * シリーズに関する処理／関数群
 */



/**
 * シリーズに属しているか否かを返す。属している場合は親ID
 *
 * @param WP_Post $post
 * @return int
 */
function is_series( $post = null ){
    $post = get_post($post);
    return 'series' == get_post_type($post->post_parent) ? $post->post_parent : 0;
}


/**
 * シリーズに属している場合にシリーズページへのリンクを返す
 *
 * @param string $pre
 * @param string $after
 * @param WP_Post $post
 */
function the_series($pre = '', $after = '', $post = null){
    $series = is_series($post);
    if( $series ){
        $series = get_post($series);
        echo $pre.'<a href="'.get_permalink($series->ID).'">'.apply_filters("the_title", $series->post_title).'</a>'.$after;
    }
}

/**
 * Get all user for series
 *
 * @param null|WP_Post|int $post
 *
 * @return array
 */
function get_series_authors($post = null){
	global $wpdb;
	$post = get_post($post);
	$query = <<<SQL
		SELECT u.* FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->posts} AS p
		ON u.ID = p.post_author
		WHERE p.post_parent = %d
		GROUP BY u.ID
SQL;
	$users = [];
	foreach( $wpdb->get_results($wpdb->prepare($query, $post->ID)) as $row ){
		$users[] = new WP_User($row);
	}
	return $users;
}


/**
 * リダイレクトされるのを防ぐ
 *
 * @param string $redirect_url
 * @return string
 */
add_filter('redirect_canonical', function($redirect_url){
    if( is_singular('series') && false !== strpos($_SERVER['REQUEST_URI'], '/page/') ){
        return false;
    }else{
        return $redirect_url;
    }
} );

/**
 * 序文・あとがきの表示を出す
 */
add_action('edit_form_after_title', function(WP_Post $post){
	if( 'series' == $post->post_type ){

		echo <<<HTML
<h2><i class="dashicons dashicons-edit"></i> あとがき</h2>
<p class="description">
ePubにした際のあとがきとして表示されます。<strong>空白の場合、あとがきは表示されません。</strong>
</p>
HTML;
	}
}, 1000);

/**
 * エディターの設定を変更する
 *
 * @param array $settings
 * @param string $editor_id
 */
add_filter('wp_editor_settings', function( array $settings, $editor_id ){
	$screen = get_current_screen();
	if( 'series' == $screen->post_type ){
		$settings['media_buttons'] = false;
	}
	return $settings;
}, 10, 2);

/**
 * ePub設定のメタボックスを追加
 */
add_action('add_meta_boxes', function($post_type){
	if( 'series' == $post_type ){

		//
		// シリーズ詳細情報
		//
		add_meta_box('series-epub', 'シリーズ詳細', function( WP_Post $post){
			wp_nonce_field('edit_epub', '_seriesepubnonce');
			$_old_post = $post;
			$users = get_series_authors($post);
			$editor = new WP_User($post->post_author);
			$series_query = new WP_Query(   [
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
					<strong>著者　：</strong> <?= implode(', ', array_map(function( WP_User $user ){
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
		}, 'series', 'normal', 'high');


		//
		// ePub用プレビューリンク
		//
		add_meta_box('series-epub-preview', 'ePubプレビュー', function( WP_Post $post ){
			?>
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
				$sub_query = new WP_Query([
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
					$endpoint = home_url("epub/preview/content/{$post->ID}/", 'https');
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
					?>
					<div class="epub-publish-action">
<!--						<a class="button-primary" target="epub-publisher" href="--><?//= home_url("epub/publish/{$post->ID}", 'https') ?><!--">書き出し</a>-->
						<a class="button-primary" target="_blank" href="<?= home_url("epub/publish/{$post->ID}", 'https') ?>">書き出し</a>						<iframe name="epub-publisher" style="display: none"></iframe>
					</div>
					<?php
				}
			?>
			<?php ?>
			<?php
		}, 'series', 'side', 'low');
	}
});


