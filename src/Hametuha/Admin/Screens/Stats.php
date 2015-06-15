<?php

namespace Hametuha\Admin\Screens;


use WPametu\UI\Admin\Screen;

class Stats extends Screen
{

	protected $title = 'アクセス解析';

	protected $slug = 'hametu-stats';

	protected $position = 3;

	protected $template = 'stats';

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
		wp_enqueue_script('hametu-analytics', get_stylesheet_directory_uri().'/assets/js/dist/admin/analytics.js',
			['google-jsapi', 'jquery-ui-datepicker-i18n'], hametuha_version(), true);
	}

}
