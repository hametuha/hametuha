<?php


namespace Hametuha\Commands;

use WPametu\Utility\Command;

/**
 * Test commands
 *
 * @package Hametuha\Commands
 */
class TestCommands extends Command {

	const COMMAND_NAME = 'ametu-test';

	/**
	 * Send test mail
	 *
	 * ## OPTIONS
	 *
	 * <to>
	 * : Mail address
	 *
	 * <subject>
	 * : Test mail title
	 *
	 * ## EXAMPLES
	 *
	 *     wp test convert
	 *
	 * @synopsis <to> <subject>
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	function mail( $args, $assoc_args ) {
		list( $to, $subject ) = $args;
		$body = <<<TEXT
This is a test mail.
この部分は日本語で書かれています。
正しく届いているでしょうか。
TEXT;

		wp_mail( $to, $subject, $body );
	}

}
