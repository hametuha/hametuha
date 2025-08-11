<?php

namespace Hametuha\Dashboard;


use Hametuha\Hashboard\Pattern\Screen;

class Read extends Screen {

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return '読む';
	}

	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		switch ( $page ) {
			default:
				return 'あなたの読書履歴です。';
				break;
		}
	}


	/**
	 * Should return unique URL slug.
	 *
	 * @return string
	 */
	public function slug() {
		return 'read';
	}
}
