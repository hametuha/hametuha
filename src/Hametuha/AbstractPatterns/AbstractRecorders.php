<?php

namespace Hametuha\AbstractPatterns;

use WPametu\Pattern\Singleton;
use Hametuha\Hooks\Analytics;

/**
 * Class AbstractRecorders
 *
 * @package hametuha
 * @property Analytics $analytics
 */
abstract class AbstractRecorders extends Singleton {

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = [] ) {
		$this->init();
	}

	/**
	 * Executed inside constructor.
	 *
	 * @return void
	 */
	abstract protected function init();

	/**
	 * Record user event.
	 *
	 * @param string $action
	 * @param int $user_id
	 * @param string $category
	 */
	public function save_user_event( $action, $user_id, $category, $label ) {
		$user_hash =  cookie_tasting_get( 'uuid' ) ?: cookie_tasting_get_uuid( $user_id );
		$this->analytics->measurement->event( [
			'ea' => $action,
			'ec' => $category,
			'el' => $label,
			'uid' => $user_hash,
			Analytics::DIMENSION_UID => $user_hash,
		] );
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch( $name ) {
			case 'analytics':
				return Analytics::get_instance();
				break;
		}
	}
}
