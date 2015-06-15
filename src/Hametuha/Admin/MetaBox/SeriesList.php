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

	protected $nonce_key = '_seriesordernonce';

	protected $nonce_action = 'series-order-update';

	/**
	 * Register Ajax action
	 */
	public function adminInit(){
		if( $this->isAjax() ){
			add_action('wp_ajax_series_list', [$this, 'seriesList']);
		}
		add_action("admin_enqueue_scripts", function($page){
			$screen = get_current_screen();
			if( 'post' == $screen->base && 'series' == $screen->post_type ){
				wp_enqueue_script('series-helper', get_stylesheet_directory_uri().'/assets/js/dist/admin/series-helper.js', ['jquery-ui-sortable', 'backbone', 'underscore', 'jquery-effects-highlight'], filemtime(get_stylesheet_directory().'/assets/js/dist/admin/series-helper.js'), true);
			}
		});
	}

	/**
	 * Save order and override title
	 *
	 * @param \WP_Post $post
	 */
	public function savePost( \WP_Post $post ) {
		foreach( ['series_order', 'series_override'] as $key ){
			if( $this->input->post($key) && is_array($this->input->post($key)) ){
				foreach( $this->input->post($key) as $id => $value ){
					if( !current_user_can('edit_post', $id) ){
						continue;
					}
					switch( $key ){
						case 'series_order':
							$this->series->update_order($id, $value);
							clean_post_cache($id);
							break;
						case 'series_override':
							update_post_meta($id, '_series_override', $value);
							break;
						default:
							// do nothing
							break;
					}
				}
			}
		}
	}


	/**
	 * Update serires list
	 */
	public function seriesList(){
		try{
			if( !$this->input->verify_nonce($this->nonce_action, $this->nonce_key) ){
				throw new \Exception('不正なアクセスです。', 500);
			}
			$post_id = $this->input->post('post_id');
			if( !$post_id || !current_user_can('edit_post', $post_id) ){
				throw new \Exception('あなたには権限がありません。', 500);
			}
			$result = wp_update_post([
				'ID' => $post_id,
			    'post_parent' => 0,
			], true);
			if( !$result || is_wp_error($result) ){
				throw new \Exception('更新できませんでした。', 500);
			}
			$json = [
				'success' => true,
			    'message' => 'OK',
			    'code' => 200,
			];
		}catch ( \Exception $e ){
			$json = [
				'success' => false,
			    'message' => $e->getMessage(),
			    'code' => $e->getCode(),
			];
		}
		wp_send_json($json);
	}

	/**
	 * Render list
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	protected function renderList( \WP_Post $post ){
		$title = get_the_title($post);
		$override = esc_attr(get_post_meta($post->ID, '_series_override', true));
		$date = mysql2date(get_option('date_format'), $post->post_date);
		$author = esc_html(get_the_author_meta('display_name', $post->post_author));
		$edit_url = get_edit_post_link($post->ID);
		return <<<HTML
		<li>
			<input type="hidden" name="series_order[{$post->ID}]" value="{$post->menu_order}" />
			<strong class="series-title">{$title}</strong>
			<span class="author-name">{$author}</span>
			<span class="series-date">（{$date}）</span>
			<a href="{$edit_url}" target="_blank">編集</a> |
			<a href="#" data-id="{$post->ID}" class="button--delete">除外</a>
			<br />
			<label>
				<input type="text" name="series_override[{$post->ID}]" value="{$override}" placeholder="上書きする場合はタイトルを入力" />
			</label>
			<span class="dashicons dashicons-menu"></span>
		</li>
HTML;

	}

	/**
	 * @param \WP_Post $post
	 * @param array $screen
	 */
	public function doMetaBox( \WP_Post $post, array $screen ) {
		$users = get_series_authors($post);
		$editor = new \WP_User($post->post_author);
		$series = get_posts(   [
			'post_type' => 'post',
			'post_parent' => $post->ID,
			'posts_per_page' => -1,
			'orderby' => [
				'menu_order' => 'DESC',
				'post_date' => 'ASC',
			],
		    'suppress_filters' => false,
		]);
		?>
			<p class="description">
				この作品集に登録されている作品の一覧です。並び順の初期値は古い順です。目次に表示されるタイトルは上書きすることができます。<br />
				<strong>例：</strong>
			</p>
			<ol id="series-posts-list" data-endpoint="<?= admin_url('admin-ajax.php') ?>" data-post-id="<?= $post->ID ?>" data-nonce="<?= wp_create_nonce($this->nonce_action) ?>">
			<?php
				foreach( $series as $s ){
					echo $this->renderList($s);
				}
			?>
			</ol>
			<hr />
			<table class="form-table">
				<tr>
					<th>編集者</th>
					<td>
						<?= esc_html($editor->display_name) ?>
					</td>
				</tr>
			</table>
		<?php
	}


}
