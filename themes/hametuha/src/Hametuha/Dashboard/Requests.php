<?php

namespace Hametuha\Dashboard;

use Hametuha\Hashboard\Pattern\Screen;

class Requests extends Screen {

	protected $icon = 'verified_user';

	/**
	 * @return string
	 */
	public function slug() {
		return 'requests';
	}

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return 'リクエスト';
	}

	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		switch ( $page ) {
			case 'collaborations':
				return '作品集の寄稿者・共同編集者として招待されたリクエストです。';
			default:
				return '';
		}
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'collaborations' => 'コラボレーション',
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		?>
		<div id="hametuha-requests" data-type="<?php echo esc_attr( $page ); ?>">
		</div>
		<?php
	}

	/**
	 * Footer action
	 */
	public function footer( $child = '' ) {
		wp_enqueue_script( 'hametuha-hb-requests' );
	}
}
