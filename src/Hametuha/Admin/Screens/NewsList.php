<?php

namespace Hametuha\Admin\Screens;


use Hametuha\Admin\Table\NewsListTable;
use WPametu\UI\Admin\Screen;


class NewsList extends Screen {

	protected $menu_title = '執筆実績';

	protected $title = 'ニュース執筆実績';

	protected $slug = 'hamenew-score';

	protected $parent = 'edit.php?post_type=news';

	protected $caps = 'edit_posts';

	protected $icon = 'dashicons-analytics';

	/**
	 * Executed on admin_init
	 */
	public function adminInit() {
		// Do nothing
	}

	/**
	 * Enqueue scripts
	 */
	protected function enqueueScript() {
	}

	/**
	 * Load templates
	 */
	protected function content() {
		printf( '<form action="%s" method="get">', admin_url( 'edit.php' ) );
		echo <<<HTML
			<input type="hidden" name="post_type" value="news" />
			<input type="hidden" name="page" value="{$this->slug}" />
HTML;

		$table = new NewsListTable();
		$table->prepare_items();
		$table->views();
		$table->search_box( '検索', 's' );
		ob_start();
		$table->display();
		$content = preg_replace( '/<input[^>]+_wp_http_referer[^>]+>/u', '', ob_get_contents() );
		ob_end_clean();
		echo $content;
		echo '</form>';
	}
}
