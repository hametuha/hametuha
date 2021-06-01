<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;


/**
 * Approval email
 *
 * @package Hametuha\Notifications\Emails
 */
class CollaboratorMarginUpdate extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML

作品集『-title-』での報酬が変更になりました。

ロイヤリティの -margin-%
-url-

不明な点は著者にお尋ねください。

HTML;
	}

	/**
	 * Returns title.
	 *
	 * @return string
	 */
	protected function get_subject() {
		return '破滅派 「-title-」報酬額変更';
	}

	/**
	 * Register hooks here.
	 */
	public static function register() {
		add_action(
			'hametuha_collaborators_updated',
			function( $collaborator, $series_id, $margin ) {
				static::exec(
					[
						$collaborator->ID => [
							'title'  => get_the_title( $series_id ),
							'url'    => home_url( 'dashboard/requests/collaborations' ),
							'margin' => $margin,
						],
					]
				);
			},
			10,
			3
		);
	}


}
