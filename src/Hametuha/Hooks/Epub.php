<?php

namespace Hametuha\Hooks;


use Hametuha\Model\CompiledFiles;
use Hametuha\Model\Series;
use WPametu\Pattern\Singleton;

/**
 * ePub helper
 *
 * @package hametuha
 * @property CompiledFiles $files
 * @property Series        $series
 */
class Epub extends Singleton {

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = [] ) {
		// Avoid user from resign.
		add_filter( 'nlmg_validate_user', [ $this, 'validate_leaving' ], 10, 2 );
		// Add extra caps.
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
	}

	/**
	 * Check if current user can leave.
	 *
	 * @param \WP_Error $error
	 * @param int       $user_id
	 * @return \WP_Error
	 */
	public function validate_leaving( $error, $user_id ) {
		$published_count = $this->series->get_owning_series( $user_id );
		if ( $published_count ) {
			$error->add( 'having_published_epubs', sprintf( '販売中の電子書籍が%d作あります。販売停止申請を行ってから退会してください。', $published_count ) );
		}
		return $error;
	}

	/**
	 * Add custom caps.
	 *
	 * @param array $caps
	 * @param string $cap
	 * @param int $user_id
	 * @param array $args
	 * @return array
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		switch ( $cap ) {
			case 'publish_epub':
				list( $post_id ) = $args;
				$caps = $this->remove_cap( $caps, $cap );
				$post = get_post( $post_id );
				if ( $post->post_author != $user_id ) {
					$caps[] = 'edit_others_posts';
				} elseif ( hametuha_is_secret_book( $post ) ) {
					$caps[] = 'edit_posts';
				} else {
					$caps[] = 'manage_options';
				}
				break;
			case 'get_epub':
				$caps = $this->remove_cap( $caps, $cap );
				list( $file_id ) = $args;
				$file = $this->files->get_file( $file_id );
				if ( ! $file || ! ( ( $post = get_post( $file->post_id ) ) && 'series' == $post->post_type ) ) {
					$caps[] = 'do_not_allow';
				} else if ( $post->post_author != $user_id ) {
					$caps[] = 'edit_others_posts';
				} else if ( hametuha_is_secret_book( $post ) ) {
					$caps[] = 'edit_posts';
				} else {
					$caps[] = 'manage_options';
				}
				break;
			default:
				// Do nothing.
				break;
		}
		return $caps;
	}

	/**
	 * Remove a cap from caps.
	 *
	 * @param array $caps
	 * @param string $cap
	 * @return array
	 */
	public function remove_cap( $caps, $cap ) {
		$index = array_search( $cap, $caps );
		if ( false !== $index ) {
			array_splice( $caps, $index, 1 );
		}
		return $caps;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'series':
				return Series::get_instance();
			case 'files':
				return CompiledFiles::get_instance();
			default:
				return null;
		}
	}


}
