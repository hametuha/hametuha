<?php

namespace Hametuha\Admin\Screens;


use Hametuha\Admin\Table\CompiledFileTable;
use WPametu\UI\Admin\Screen;


class CompiledFileList extends Screen
{

	protected $menu_title = 'ファイル';

	protected $title = '出力されたファイル';

	protected $slug = 'hamepub-files';

	protected $parent = 'edit.php?post_type=series';

	protected $caps = 'edit_others_posts';

	protected $icon = 'dashicons-format-aside';

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
		add_thickbox();
	}

	/**
	 * Load templates
	 */
	protected function content() {
		printf('<form action="%s" method="get">', admin_url('edit.php'));
		echo <<<HTML
			<iframe name="file-downloader" style="display: none;"></iframe>
			<input type="hidden" name="post_type" value="series" />
			<input type="hidden" name="page" value="hamepub-files" />
HTML;

		$table = new CompiledFileTable();
		$table->prepare_items();
		$table->views();
		$table->search_box('検索', 's');
		ob_start();
		$table->display();
		$content = preg_replace('/<input[^>]+_wp_http_referer[^>]+>/u', '', ob_get_contents());
		ob_end_clean();
		echo $content;
		echo '</form>';
	}
}
