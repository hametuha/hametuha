<?php


namespace Hametuha\Commands;

use Hametuha\Model\Notifications;
use WPametu\Service\Akismet;
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
	public function mail( $args, $assoc_args ) {
		list( $to, $subject ) = $args;
		$body = <<<TEXT
This is a test mail.
この部分は日本語で書かれています。
正しく届いているでしょうか。
TEXT;

		wp_mail( $to, $subject, $body );
	}

	/**
	 * Send notification
	 *
	 * <message>
	 * : Message to send.
	 *
	 * ## EXAMPLES
	 *
	 *  wp test notification 'こんにちは！'
	 *
	 * @synopsis <message>
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function notification( $args, $assoc_args ) {
		list( $msg ) = $args;
		if ( Notifications::get_instance()->add_general( 0, 0, $msg, get_option( 'admin_email' ) ) ) {
			$this->s( 'メッセージを送信しました。' );
		} else {
			$this->e( '送信に失敗しました。' );
		}
	}

	/**
	 * Post message to facebook
	 *
	 * ## EXAMPLES
	 *
	 * wp ametu-test fb 'こんにちは！'
	 *
	 * @synopsis <message> [--link=<link>]
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function fb( $args, $assoc_args ) {
		list( $message ) = $args;
		$param = [
			'message' => $message,
		];
		if ( isset( $assoc_args['link'] ) ) {
			$param['link'] = $assoc_args['link'];
		}
		$response = minico_share( $param );
		if ( is_wp_error( $response ) ) {
			var_dump( $response );
			self::e( sprintf( '%s: %s', $response->get_error_code(), $response->get_error_message() ) );
		}
		var_dump( $response );
		self::s( 'Message sent.' );
	}


	/**
	 * Get ranking
	 *
	 * ## EXAMPLES
	 *
	 * wp ametu-test ga '2016-09-06' '2016-09-06'
	 *
	 * @synopsis <start> <end>
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function ga( $args, $assoc_args ) {
		list( $start, $end ) = $args;
		$rankings = hametuha_ga_ranking( $start, $end, [
			'dimensions' => 'ga:pageTitle,ga:pagePath',
		] );
		if ( is_wp_error( $rankings ) ) {
			self::e( $rankings->get_error_message() );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'Rank', 'Title', 'Path', 'PV' ] );
		$body = [];
		foreach ( $rankings as $index => list( $title, $path, $pv ) ) {
			$body[] = [
				$index + 1,
				current( explode( ' | ', $title ) ),
				home_url( $path ),
				$pv,
			];
		}
		$table->setRows( $body );
		$table->display();
	}

	/**
	 * Get the list of instant articles
	 *
	 * @synopsis [--d]
	 */
	public function articles( $args, $assoc ) {
		$token = minico_access_token();
		if ( is_wp_error( $token ) ) {
			self::e( $token->get_error_message() );
		}
		$result = minico_fb_request( 'instant_articles', 'GET', [
			'access_token' => $token,
			'development_mode' => isset( $assoc['d'] ) && $assoc['d'],
		] );
		if ( is_wp_error( $result ) ) {
			self::e( $result->get_error_message() );
		} else {
			$table = new \cli\Table();
			$table->setHeaders( [ 'ID', 'URL' ] );
			$rows = [];
			foreach ( $result['data'] as $post ) {
				$rows[] = [ $post['id'], $post['canonical_url'] ];
			}
			$table->setRows( $rows );
			$table->display();
		}
	}

	/**
	 * Check if mail user is spam
	 *
	 * ## EXAMPLES
	 *
	 *     wp test spam_list
	 *
	 */
	public function spam_list( $args, $assoc_args ) {
		global $wpdb;
		$query                = <<<SQL
			SELECT
				s.*,
				u.ID AS user_id,
				u.user_registered
			FROM {$wpdb->prefix}easymail_subscribers AS s
			LEFT JOIN {$wpdb->users} AS u
			ON s.email = u.user_email
			ORDER BY s.ID ASC
SQL;
		$subscriber_to_delete = [];
		$users                = $wpdb->get_results( $query );
		static::l( sprintf( 'Checking %d users...', count( $users ) ) );

		foreach ( $users as $user ) {
			$data = [
				'user_ip'              => $user->ip_address,
				'user_agent'           => '',
				'permalink'            => home_url( '/login/?action=register', 'https' ),
				'comment_type'         => 'signup',
				'comment_author'       => $user->name,
				'comment_author_email' => $user->email,
				'comment_date_gmt'     => strtotime( $user->join_date ),
			];
			if ( ! $user->user_id ) {
				// Not registered user.
				// Dubious!
			} else {
				// Registered user.
				$cap = get_user_meta( $user->user_id, "{$wpdb->prefix}capabilities", true );
				if ( isset( $cap['pending'] ) && $cap['pending'] ) {
					// Pending. this may be spam.
					// If 3 days passed registered date passed, delete this.
					$registered = new \DateTime( $user->user_registered );
					$now        = new \DateTime( current_time( 'mysql' ) );
					if ( $registered->diff( $now, true )->days > 3 ) {
						// Delete user and continue.
						$subscriber_to_delete[] = $user->ID;
						continue;
					} else {
						// Check this is spam.
					}
				} else {
					// O.K. it's not spam.
					continue;
				}
			}
			// Here you are, you should be tested!
			$result = Akismet::is_spam( $data );
			if ( is_wp_error( $result ) ) {
				// Oops, failed!
				static::w( $result->get_error_message() );
			} elseif ( $result ) {
				// This is explicitly spam
				static::w( sprintf( 'ID %d is spam', $user->ID ) );
				$subscriber_to_delete[] = $user->ID;
			} else {
				static::l( sprintf( 'ID %d is ham', $user->ID ) );
			}
			sleep( 1 );
		}
		static::l( '' );
		// Delete subscribers!
		static::l( sprintf( 'Delete %d subscribers!', count( $subscriber_to_delete ) ) );
		$ids   = implode( ', ', $subscriber_to_delete );
		$query = <<<SQL
			DELETE FROM {$wpdb->prefix}easymail_subscribers
			WHERE ID IN ({$ids})
SQL;
		if ( ! $wpdb->query( $query ) ) {
			static::w( sprintf( 'Failed to delete %d records...', count( $subscriber_to_delete ) ) );
		}
		static::l( '' );
		static::s( 'Done!' );
	}

}
