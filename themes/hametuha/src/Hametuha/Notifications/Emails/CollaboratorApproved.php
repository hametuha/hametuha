<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;


/**
 * Approval email
 *
 * @package Hametuha\Notifications\Emails
 */
class CollaboratorApproved extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML

作品集『-title-』への参加を -collaborator- さんが承認しました。
次回の売上登録から報酬がシェアされます。

-url-

HTML;
	}

	/**
	 * Returns title.
	 *
	 * @return string
	 */
	protected function get_subject() {
		return '破滅派 -collaborator- さんが「-title-」 に参加しました';
	}

	/**
	 * Register hooks here.
	 */
	public static function register() {
		add_action( 'hametuha_collaborators_approved', function ( $collaborator, $post_id ) {
			static::exec( [
				get_post( $post_id )->post_author => [
					'collaborator' => $collaborator->display_name,
					'url'          => get_edit_post_link( $post_id, 'email' ),
					'title'        => get_the_title( $post_id ),
				],
			] );
		}, 10, 2 );
	}
}
