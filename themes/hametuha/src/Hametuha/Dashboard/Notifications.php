<?php

namespace Hametuha\Dashboard;

use Hametuha\Hashboard\Pattern\Screen;

class Notifications extends Screen {

	protected $icon = 'notifications';

	/**
	 * @return string
	 */
	public function slug() {
		return 'notifications';
	}

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return 'お知らせ';
	}

	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		switch ( $page ) {
			case 'works':
				return 'あなたの投稿に関するお知らせです。';
			case 'general':
				return '破滅派運営からのお知らせです。';
			default:
				return 'すべてのお知らせです。';
		}
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'all'     => 'すべて',
			'works'   => 'あなたの作品',
			'general' => '運営から',
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		?>
		<div id="hametuha-notifications" data-type="<?php echo esc_attr( $page ); ?>">
		</div>
		<?php
	}

	/**
	 * Footer action
	 */
	public function footer( $child = '' ) {
		wp_enqueue_script( 'hametuha-hb-notifications' );
	}
}
