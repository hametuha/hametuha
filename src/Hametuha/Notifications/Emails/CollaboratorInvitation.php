<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;

/**
 * Collaborator invitaion
 *
 * @package Hametuha\Notifications\Emails
 */
class CollaboratorInvitation extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML


作品集『-title-』にコラボレーターとして招待されています。
収益はロイヤリティのうち -revenue - です。
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
}
