<?php

namespace Hametuha\Dashboard;


use Hametuha\Hashboard\Pattern\Screen;

/**
 * List of your works.
 */
class Followers extends Screen {

	protected $icon = 'handshake';

	public function slug() {
		return 'friends';
	}

	public function label() {
		return __( 'フォロワー', 'hametuha' );
	}

	public function description( $page = '' ) {
		return __( '破滅派でフォローしている人です。', 'hametuha' );
	}

	/**
	 * Render HTML
	 *
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		wp_enqueue_script( 'hametuha-hb-followers' );
		?>
		<div id="hametuha-follower-container"></div>
		<?php
	}
}
