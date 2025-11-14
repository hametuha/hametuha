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
		switch ( $page ) {
			case 'audiences':
				return __( 'あなたの作品を読んだ読者の傾向を表示しています。', 'hametuha' );
			case 'traffic':
				return __( '読者がどうやってあなたの作品にたどり着いたかを表示しています。', 'hametuha' );
			default:
				return __( '人気の記事を発表しています。', 'hametuha' );
		}
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'popular'   => __( '人気の作品', 'hametuha' ),
			'audiences' => __( '読者の傾向', 'hametuha' ),
			'traffic'   => __( '流入経路', 'hametuha' ),
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		switch ( $page ) {
			case 'audiences':
			case 'traffic':
				wp_enqueue_script( 'hametuha-hb-stats-' . $page );
				hameplate( 'templates/dashboard/analytics', '', [
					'target' => $page,
				] );
				break;
			default:
				wp_enqueue_script( 'hametuha-hb-stats-pv' );
				hameplate( 'templates/dashboard/analytics', 'access', [
					'endpoint' => rest_url( 'hametuha/v1/stats/access/me' ),
				] );
				break;
		}
		hameplate( 'templates/dashboard/footer', '', [
			'slug' => 'dashboard-analytics-footer',
		] );
	}
}
