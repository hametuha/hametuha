<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;


/**
 * Approval email
 *
 * @package Hametuha\Notifications\Emails
 */
class CollaboratorDenial extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML

作品集『-title-』への参加を -collaborator- さんが辞退しました。

-url-

HTML;
	}

	/**
	 * Returns title.
	 *
	 * @return string
	 */
	protected function get_subject() {
		return '破滅派 -collaborator- さんが「-title-」への参加を辞退しました';
	}

	/**
	 * Register hooks here.
	 */
	public static function register() {
		add_action( 'hametuha_collaborators_denied', function( $user, $post ) {
			static::exec( [
				$post->post_author => [
					'collaborator' => $user->display_name,
					'url'          => get_edit_post_link( $post->ID, 'email' ),
					'title'        => get_the_title( $post ),
				],
			] );
		}, 10, 2 );
	}


}