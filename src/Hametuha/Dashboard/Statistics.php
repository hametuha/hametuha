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
		return '直近一ヶ月の統計情報を表示します。';
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [];
//			'statistics' => 'アクセス',
//			'readers'    => '読者層',
//			'traffic'    => '集客経路',
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		switch ( $page ) {
			case 'readers':
				hameplate( 'templates/dashboard/analytics', 'readers', [
					'page' => $page,
					'endpoint' => rest_url( "hametuha/v1/sales/history/me" ),
				] );
				break;
			case 'payments':
				hameplate( 'templates/dashboard/sales', 'payments', [
					'endpoint' => rest_url( "hametuha/v1/sales/payments/me" ),
				] );
				break;
			default:
				wp_enqueue_script( 'hametuha-hb-stats-pv' );
				hameplate( 'templates/dashboard/analytics', 'access', [
					'endpoint' => rest_url( "hametuha/v1/stats/access/me" ),
				] );
				break;
		}
		hameplate( 'templates/dashboard/footer', '', [
			'slug' => 'dashboard-analytics-footer',
		] );
	}


}
