<?php

namespace Hametuha\Hooks;


use WPametu\Pattern\Singleton;

/**
 * Editor hooks
 *
 * @package hametuha
 */
class Editor extends Singleton {

	public $post_type_to_exclude = [ 'post', 'news', 'series', 'announcement' ];

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = [] ) {
		parent::__construct();
		add_filter( 'use_block_editor_for_post_type', [ $this, 'filter_block_editor' ], 10, 2 );
	}

	/**
	 * Exclude post type from gutenberg.
	 *
	 * @param bool   $use
	 * @param string $post_type
	 * @return bool
	 */
	public function filter_block_editor( $use, $post_type ) {
		return ! in_array( $post_type, $this->post_type_to_exclude );
	}

}
