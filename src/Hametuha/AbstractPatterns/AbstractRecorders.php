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
