<?php

namespace Hametuha\Hooks;


use WPametu\Pattern\Singleton;

/**
 * Editor hooks
 *
 * @package hametuha
 */
class Editor extends Singleton {

	public $post_type_to_exclude = [ 'post', 'series', 'announcement' ];

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = [] ) {
		add_filter( 'use_block_editor_for_post_type', [ $this, 'filter_block_editor' ], 10, 2 );
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ] );
		add_filter( 'display_post_states', [ $this, 'post_state' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor_helper' ] );
	}

	/**
	 * Exclude post type from gutenberg.
	 *
	 * @param bool   $use
	 * @param string $post_type
	 * @return bool
	 */
	public function filter_block_editor( $use, $post_type ) {
		return ! in_array( $post_type, $this->post_type_to_exclude, true );
	}

	/**
	 * Register All Blocks.
	 *
	 * assets-src/blocks/<名前>/ を wp-scripts でビルドした assets/blocks/<名前>/（block.json 付き）を登録する。
	 */
	public function register_blocks() {
		foreach ( glob( get_template_directory() . '/assets/blocks/*/block.json' ) as $block_json ) {
			register_block_type( dirname( $block_json ) );
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
		$fp  = fopen( $path, 'r' );
		$len = 0;
		while ( $len < 10 && ( $line = fgets( $fp, 1024 ) ) ) {
			++$len;
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
						$post_states['one-columns'] = '1カラム';
						break;
				}
				break;
			case 'post':
				if ( 0 < $post->post_parent && get_post( $post->post_parent ) ) {
					$link                       = get_edit_post_link( $post->post_parent );
					$title                      = get_the_title( $post->post_parent );
					$post_states['post-parent'] = $link ? sprintf( '<a href="%s">%s</a>', $link, esc_html( $title ) ) : esc_html( $title );
				}
				break;
		}
		return $post_states;
	}

	/**
	 * Enqueue classic editor helper.
	 *
	 * @param string $page Current page.
	 * @return void
	 */
	public function enqueue_editor_helper( $page ) {
		$screen = get_current_screen();
		if ( 'post' !== $screen->base || $screen->is_block_editor() ) {
			// This is not classic editor.
			return;
		}
		// If current user is editor, activate select2.
		if ( current_user_can( 'edit_others_posts' ) ) {
			wp_enqueue_script( 'hametuha-author-selector' );
			wp_enqueue_style( 'select2' );
		}
	}
}
