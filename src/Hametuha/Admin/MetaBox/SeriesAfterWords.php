<?php

namespace Hametuha\Admin\MetaBox;

/**
 * Show after words' title
 *
 * @package Hametuha\Admin\MetaBox
 */
class SeriesAfterWords extends SeriesBase
{

	/**
	 * @var string
	 */
	protected $hook = 'edit_form_after_title';

	/**
	 * @var int
	 */
	protected $hook_priority = 1000;

	/**
	 * @param \WP_Post $post
	 */
	public function editFormX( \WP_Post $post ) {
		/**
		 * エディターの設定を変更する
		 *
		 * @param array $settings
		 * @param string $editor_id
		 */
		add_filter('wp_editor_settings', function( array $settings, $editor_id ){
			if( 'series' == $this->screen->post_type ){
				$settings['media_buttons'] = false;
			}
			return $settings;
		}, 10, 2);
		echo <<<HTML
			<h2><i class="dashicons dashicons-edit"></i> あとがき</h2>
			<p class="description">
			ePubにした際のあとがきとして表示されます。<strong>空白の場合、あとがきは表示されません。</strong>
			</p>
HTML;
	}


}