<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;

/**
 * Collaborator invitation.
 *
 * @package hametuha
 */
class CollaboratorInvitation extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML


作品集『-title-』に-type-として招待されています。
収益はロイヤリティのうち -revenue-% です。
承認する場合は以下のURLに移動してください。

-url-

作品のURL: -post_url-

HTML;
	}

	/**
	 * Returns title.
	 *
	 * @return string
	 */
	protected function get_subject() {
		return '破滅派 「-title-」 へのコラボレーター招待';
	}

	/**
	 * Register hooks here.
	 */
	public static function register() {
		add_action(
			'hametuha_collaborators_added',
			function( $user_id, $post, $margin, $type, $label ) {
				static::exec(
					[
						$user_id => [
							'title'    => get_the_title( $post ),
							'revenue'  => $margin,
							'type'     => $label,
							'url'      => home_url( 'dashboard/requests/collaborations' ),
							'post_url' => get_permalink( $post ),
						],
					]
				);
			},
			10,
			5
		);
	}


}
