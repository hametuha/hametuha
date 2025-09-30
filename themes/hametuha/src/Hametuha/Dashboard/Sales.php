<?php

namespace Hametuha\Dashboard;


use Hametuha\Hashboard\Pattern\Screen;

class Sales extends Screen {

	protected $icon = 'attach_money';

	/**
	 * Should return unique URL slug.
	 *
	 * @return string
	 */
	public function slug() {
		return 'sales';
	}

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return '売上';
	}

	/**
	 * Head action
	 */
	public function head( $child = '' ) {
		wp_enqueue_script( 'hametuha-hb-payment-table' );
	}


	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		switch ( $page ) {
			case 'deposit':
				return '支払いが確定している未払いの報酬';
				break;
			case 'rewards':
				return 'あなたがこれまで破滅派から受け取った報酬の履歴';
				break;
			case 'payments':
				return 'これまで破滅派がお支払いした入金履歴';
				break;
			default:
				return '電子書籍の売り上げ記録';
				break;
		}
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'sales'    => '電子書籍売上',
			'deposit'  => '確定報酬',
			'rewards'  => '報酬履歴',
			'payments' => '入金履歴',
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		switch ( $page ) {
			case 'rewards':
			case 'deposit':
				hameplate( 'templates/dashboard/sales', 'rewards', [
					'page'     => $page,
					'endpoint' => rest_url( 'hametuha/v1/sales/rewards/me' ),
				] );
				break;
			case 'payments':
				hameplate( 'templates/dashboard/sales', 'payments', [
					'endpoint' => rest_url( 'hametuha/v1/sales/payments/me' ),
				] );
				break;
			default:
				hameplate( 'templates/dashboard/sales', 'graph', [
					'endpoint' => rest_url( 'hametuha/v1/sales/history/me' ),
				] );
				break;
		}
		hameplate( 'templates/dashboard/footer', '', [
			'slug' => 'dashboard-sales-footer',
		] );
	}


}
