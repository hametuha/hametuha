<?php

namespace Hametuha\Dashboard;


use Hametuha\Hashboard\Pattern\Screen;

/**
 * Histroy of readings.
 */
class Readings extends Screen {

	protected $icon = 'book';

	/**
	 * Should return unique URL slug.
	 *
	 * @return string
	 */
	public function slug() {
		return 'reading';
	}

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return __( '読書履歴', 'hametuha' );
	}

	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		switch ( $page ) {
			case 'ratings':
				return __( 'あなたが評価した作品です。', 'hametuha' );
			default:
				return __( 'あなたコメントした作品です。', 'hametuha' );
		}
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'comments' => 'コメント',
			'reviews'  => 'レビュー',
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		wp_enqueue_script( 'hametuha-hb-posts' );
		switch ( $page ) {
			case 'reviews':
				$args['post_type'] = 'review';
				break;
			default:
				$args['post_type'] = 'comment';
				break;
		}
		$args['as'] = 'reader';
		hameplate( 'templates/dashboard/post-list', '', $args );
		hameplate( 'templates/dashboard/footer', '', [
			'slug' => 'dashboard-reading-footer',
		] );
	}


}
