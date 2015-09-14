<?php

namespace Hametuha\Rest;


use Hametuha\Model\Series;
use Hametuha\Model\Sales as ModelSales;
use WPametu\API\Rest\RestTemplate;

/**
 * Class Sales
 * @package Hametuha\Rest
 * @property-read ModelSales $sales
 * @property-read Series $series
 */
class Sales extends RestTemplate {

	public static $prefix = 'sales';

	protected $title = '売上管理';

	protected $action = 'sales_reports';

	protected $content_type = 'text/html';

	protected $models = [
		'sales'  => ModelSales::class,
		'series' => Series::class,
	];

	/**
	 * トップ
	 *
	 * 現在はページビュー
	 *
	 * @param int $page
	 */
	protected function pager( $page = 1 ) {
		$this->auth_redirect();
		if ( 1 === $page ) {
			// Top page
			$this->set_data( [
				'breadcrumb' => false,
				'current'    => false,
				'graph'      => '',
				'endpoint'   => [
					'sales' => $this->nonce_url( '/sales/report/' ),
					'title' => $this->nonce_url( '/sales/report/title/' ),
				],
			] );
			$this->response();
		} else {
			$this->method_not_found();
		}
	}

	/**
	 * Report data API
	 *
	 * @param string $type
	 *
	 * @throws \Exception
	 */
	public function get_report( $type = '' ) {
		$data = [
			'options' => [],
			'data'    => [],
		];
		if ( ! current_user_can( 'edit_posts' ) ) {
			$this->error( 'あなたにはその権限がありません。', 403 );
		}
		$from = $this->input->get( 'from' );
		$to   = $this->input->get( 'to' );
		switch ( $type ) {
			case 'title':
				$data['options'] = [
					'backgroundColor' => '#fff',
					'width'           => '100%',
					'page'            => 'enable',
					'pageSize'        => 10,
				];
				$data['data'][]  = [ '日付', 'タイトル', '種別', 'ストア', '数量', 'ロイヤリティ' ];
				foreach ( $this->sales->get_title_report( $from, $to, get_current_user_id() ) as $row ) {
					$data['data'][] = [
						mysql2date( get_option( 'date_format' ), $row->date ),
						get_the_title( $row->ID ),
						$row->type,
						$row->place,
						$row->unit,
						$row->royalty,
					];
				}
				break;
			default:
				$data['options'] = [
					'legend' => [
						'position' => 'top',
					],
					'backgroundColor' => '#fff',
					'animation'       => [
						'duration' => 300,
						'easing'   => 'out',
					],
				];
				$data['data']['cols']  = [
					[
						'label' => '日付',
						'id'    => 'date',
						'type'  => 'string',
					],
					[
						'label' => '無料DL数',
						'id'    => 'free',
						'type'  => 'number',
					],
					[
						'label' => '有料DL数',
						'id'    => 'paid',
						'type'  => 'number',
					],
					[
						'label' => 'ロイヤリティ',
						'id'    => 'royalty',
						'type'  => 'number',
					],
				];
				$data['data']['rows'] = [];
				foreach ( $this->sales->get_royalty_report( $from, $to, get_current_user_id() ) as $date => $row ) {
					$data['data']['rows'][] = [
						'c' => [
							[
								'v' => $date,
								'f' => mysql2date( get_option( 'date_format' ), $date ),
							],
							[
								'v' => $row['free'],
								'f' => number_format( $row['free'] ),
							],
							[
								'v' => $row['paid'],
								'f' => number_format( $row['paid'] ),
							],
							[
								'v' => $row['royalty'],
								'f' => number_format( $row['royalty'], 2 ).' '.$row['currency'],
							],
						],
					];
				}
				break;
		}
		wp_send_json( $data );
	}

	/**
	 * Get reward
	 *
	 * @param string $page
	 * @param int $paged
	 */
	public function get_reward( $page = '', $paged = 1 ) {
		$this->auth_redirect();
		// Top page
		$this->title .= ' | 報酬';
		$this->set_data( [
			'breadcrumb' => '報酬',
			'current'    => 'reward',
			'graph'      => 'reward',
		] );
		$this->response();
	}

	/**
	 * Get reward
	 *
	 * @param string $page
	 * @param int $paged
	 */
	public function get_payment( $page = '', $paged = 1 ) {
		$this->auth_redirect();
		// Top page
		$this->title .= ' | 入金情報';
		$this->set_data( [
			'breadcrumb' => '入金情報',
			'current'    => 'payment',
			'graph'      => 'payment',
		] );
		$this->response();
	}

	/**
	 * Get reward
	 *
	 * @param string $page
	 * @param int $paged
	 */
	public function get_account( $page = '', $paged = 1 ) {
		$this->auth_redirect();
		// Top page
		$this->title .= ' | 支払い先';
		$this->set_data( [
			'breadcrumb' => '支払い先',
			'current'    => 'account',
			'graph'      => 'account',
		] );
		$this->response();
	}

	public function post_account(){
		$this->auth_redirect();
		exit;
	}

	/**
	 * Do response
	 *
	 * Echo JSON with set data.
	 *
	 * @param array $data
	 */
	protected function format( $data ) {
		wp_enqueue_script( 'hametu-analytics', get_stylesheet_directory_uri() . '/assets/js/dist/admin/analytics.js', [
			'google-jsapi',
			'jquery-ui-datepicker-i18n',
		], filemtime( get_stylesheet_directory() . '/assets/js/dist/admin/analytics.js' ), true );
		wp_enqueue_style( 'jquery-ui-mp6' );
		$this->load_template( 'templates/sales/base' );
	}


}
