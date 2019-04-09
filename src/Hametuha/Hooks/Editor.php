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
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ] );
		add_filter( 'display_post_states', [ $this, 'post_state' ], 10, 2 );
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

	/**
	 * Register All Blocks.
	 */
	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// No gutenberg.
			return;
		}
		$asset_dir = get_template_directory() . '/assets/js/dist/blocks';
		if ( ! is_dir( $asset_dir ) ) {
			return;
		}
		foreach ( scandir( $asset_dir ) as $file ) {
			if ( ! preg_match( '/^([^._].*)\.js$/u', $file, $match ) ) {
				continue;
			}
			list( $file_name, $name ) = $match;
			$block_name = 'hametuha/' . $name;
			$handle     = 'hametuha-block-' . $name;
			$file_path  = $asset_dir . '/' . $file;
			// Grab deps.
			$deps    = $this->grab_deps( $file_path );
			$version = filemtime( $file_path );
			wp_register_script( $handle, get_template_directory_uri() . '/assets/js/dist/blocks/' . $file , $deps, $version, true );
			$setting = [
				'editor_script' => $handle,
			];
			$css_path = get_template_directory() . '/assets/css/blocks/' . $name . '.css';
			if ( file_exists( $css_path ) ) {
				$css_version = filemtime( $css_path );
				wp_register_style( $handle, get_template_directory_uri() . '/assets/css/blocks' . $name . '.css', [], $css_version );
				$setting[ 'editor_style' ] = $handle;
			}
			$setting = apply_filters( 'hametuha_editor_block_setting', $setting, $handle );
			register_block_type( $block_name, $setting );
		}
	}

	/**
	 * Get JS dependencies.
	 *
	 * @param string $path File path.
	 * @return array
	 */
	public function grab_deps( $path ) {
		$deps = [];
		if ( ! file_exists( $path ) ) {
			return $deps;
		}
		// @see {wp-includes/functions.php}
		$fp = fopen( $path, 'r' );
		$len = 0;
		while ( $len < 10 && ( $line = fgets( $fp, 1024 ) ) ) {
			$len++;
			if ( preg_match( '/@wpdeps(.*)$/u', $line, $matches ) ) {
				$deps = array_filter( array_map( 'trim', explode( ',', $matches[1] ) ) );
				break;
			}
		}
		return $deps;
	}

	/**
	 * Enqueue editor assets.
	 */
	public function editor_assets() {
		$rel_path = '/assets/css/editor-style-block.css';
		wp_enqueue_style( 'hametuha-block-editor-style', get_template_directory_uri() . $rel_path, [], filemtime( get_template_directory() . $rel_path ) );
	}

	/**
	 * Add post states.
	 *
	 * @param array $post_states
	 * @param \WP_Post $post
	 * @return array
	 */
	public function post_state( $post_states, $post ) {
		switch ( $post->post_type ) {
			case 'page':
				switch ( get_page_template_slug( $post ) ) {
					case 'page-1column.php':
						$post_states[ 'one-columns' ] = '1カラム';
						break;
				}
				break;
		}
		return $post_states;
	}
}
