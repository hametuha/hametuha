<?php

namespace Hametuha\Rest;


use WPametu\API\Rest\RestTemplate;

class Statistics extends RestTemplate {

	public static $prefix = 'statistics';

	protected $title = '統計情報';

	protected $action = '';

	protected $content_type = 'text/html';

	/**
	 * トップ
	 *
	 * 現在はページビュー
	 *
	 * @param int $page
	 */
	protected function pager( $page = 1 ) {
		if ( 1 === $page ) {
			// Top page
			$this->title .= ' | アクセス | 破滅派';
			$this->set_data( [
				'breadcrumb' => false,
				'current'    => false,
				'graph'      => '',
				'endpoint'   => [
					'access'  => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaUserPvs::ACTION, \HametuhaUserPvs::get_nonce() ) ),
					'popular' => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaPopularPosts::ACTION, \HametuhaPopularPosts::get_nonce() ) ),
				],
			] );
			$this->response();
		} else {
			$this->method_not_found();
		}
	}

	public function get_readers() {
		$this->title .= ' | 読者層 | 破滅派';
		$this->set_data( [
			'breadcrumb' => '読者層',
			'current'    => 'readers',
			'graph'      => 'readers',
			'endpoint'   => [
				'users'  => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaReaderSegment::ACTION, \HametuhaReaderSegment::get_nonce() ) ),
				'region' => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaReaderRegion::ACTION, \HametuhaReaderRegion::get_nonce() ) ),
			],
		] );
		$this->response();
	}

	public function get_traffic() {
		$this->title .= ' | 集客 | 破滅派';
		$this->set_data( [
			'breadcrumb' => '読者層',
			'current'    => 'traffic',
			'graph'      => 'traffic',
			'endpoint'   => [
				'source'      => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaTrafficSource::ACTION, \HametuhaTrafficSource::get_nonce() ) ),
				'contributor' => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaTrafficContributor::ACTION, \HametuhaTrafficContributor::get_nonce() ) ),
				'keyword'     => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaTrafficKeyword::ACTION, \HametuhaTrafficKeyword::get_nonce() ) ),
			],
		] );
		$this->response();
	}

	public function get_feedback() {
		$this->title .= ' | 感想 | 破滅派';
		$this->set_data( [
			'breadcrumb' => '感想',
			'current'    => 'feedback',
			'graph'      => 'feedback',
			'endpoint'   => [
				'review'      => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaTrafficSource::ACTION, \HametuhaTrafficSource::get_nonce() ) ),
				'contributor' => admin_url( sprintf( 'admin-ajax.php?action=%s&_wpnonce=%s', \HametuhaTrafficContributor::ACTION, \HametuhaTrafficContributor::get_nonce() ) ),
			],
		] );
		$this->response();
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
		$this->load_template( 'templates/statistics/base' );
	}


}
