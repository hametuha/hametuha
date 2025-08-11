<?php

namespace Hametuha\Hooks;


use Hametuha\AbstractPatterns\AbstractRecorders;

/**
 * User edit action recorder.
 *
 * @package hametuha
 */
class RecordEditActions extends AbstractRecorders {

	/**
	 * Constructor
	 *
	 */
	protected function init() {
		// Save post publish event.
		add_action( 'transition_post_status', [ $this, 'post_transition' ], 10, 3 );
	}


	/**
	 * Record publish action.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	public function post_transition( $new_status, $old_status, $post ) {
		if ( ! ( 'publish' === $new_status && 'publish' !== $old_status ) ) {
			return;
		}
		$this->save_user_event( 'publish', $post->post_author, 'edit', $post->ID );
	}
}
