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
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		return 'あなたがこれまで破滅派で稼いだお金です。';
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
		wp_enqueue_script( 'hametuha-hb-payment-table' );
		switch ( $page ) {
			case 'rewards':
            case 'deposit':
		        hameplate( 'templates/dashboard/sales', 'rewards', [
                    'page' => $page,
                    'endpoint' => rest_url( "hametuha/v1/sales/rewards/me" ),
                ] );
				break;
			case 'payments':
				hameplate( 'templates/dashboard/sales', 'payments', [
					'endpoint' => rest_url( "hametuha/v1/sales/payments/me" ),
				] );
				break;
			default:
				hameplate( 'templates/dashboard/sales', 'graph', [
					'endpoint' => rest_url( "hametuha/v1/sales/history/me" ),
				] );
				break;
		}
		get_template_part( 'templates/dashboard/sales', 'desc' );
	}


}
