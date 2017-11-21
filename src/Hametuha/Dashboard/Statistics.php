<?php

namespace Hametuha\Dashboard;


use Hametuha\Hashboard\Pattern\Screen;

class Statistics extends Screen {

	protected $icon = 'multiline_chart';

	/**
	 * Should return unique URL slug.
	 *
	 * @return string
	 */
	public function slug() {
		return 'statistics';
	}

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return '統計情報';
	}

	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		return 'これまでの統計情報を表示します。';
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'statistics' => 'アクセス',
			'readers'    => '読者層',
			'traffic'    => '集客経路',
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {



	}


}
