<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;


/**
 * Approval email
 *
 * @package Hametuha\Notifications\Emails
 */
class CollaboratorDelete extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML

作品集『-title-』のコラボレーターとして登録されていましたが、
削除されました。
不明な点がある場合は、作者にお問い合わせください。

-url-

HTML;
	}

	/**
	 * Returns title.
	 *
	 * @return string
	 */
	protected function get_subject() {
		return '破滅派 「-title-」への招待が取り消されました';
	}

	/**
	 * Register hooks here.
	 */
	public static function register() {
		add_action( 'hametuha_collaborators_deleted', function( $user_id, $post_id ) {
			static::exec( [
				$user_id => [
					'title' => get_the_title( $post_id ),
					'url'   => get_permalink( $post_id ),
				],
			] );
		}, 10, 2 );
	}


}
