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
}