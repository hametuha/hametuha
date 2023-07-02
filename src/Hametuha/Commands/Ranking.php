<?php


namespace Hametuha\Commands;

use cli\Table;
use Hametuha\Service\GoogleAnalyticsDataAccessor;
use WPametu\Utility\Command;

/**
 * Get data from Google Analytics.
 *
 * @package hametuha
 */
class Ranking extends Command {

	const COMMAND_NAME = 'haranking';

	const UNIQ_KEY_NAME = 'record_should_be_uniq';

	/**
	 * Get popular posts.
	 *
	 * @synopsis [--post_type=<post_type>] [--limit=<limit>] [--category=<category>] [--at_least=<at_least>] [--start=<start>] [--end=<end>] [--author=<author>]
	 * @return void
	 */
	public function popular( $args, $assoc ) {
		$result = $this->ga()->popular_posts( $assoc );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		if ( empty( $result ) ) {
			\WP_CLI::error( __( '条件に該当する記録がありません。', 'hametuha' ) );
		}
		$table = new Table();
		$table->setHeaders( [ 'Path', 'Post Type', 'Category', 'PV' ] );
		foreach ( $result as $row ) {
			$table->addRow( $row );
		}
		$table->display();
	}

	/**
	 * 人気の投稿を保存する
	 *
	 * @synopsis [--date=<date>]
	 * @param array $args
	 * @param array $assoc
	 * @return void
	 */
	public function save_daily_pv( $args, $assoc ) {
		$three_days_ago = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$three_days_ago->sub( new \DateInterval( 'P3D' ) );
		$date    = $assoc['date'] ?? $three_days_ago->format( 'Y-m-d' );
		$updated = $this->ga()->save_popular_posts( 'general', [
			'start'     => $date,
			'end'       => $date,
			'post_type' => 'post',
			'limit'     => 1000,
		] );
		if ( is_wp_error( $updated ) ) {
			\WP_CLI::error( $updated->get_error_message() );
		} else {
			\WP_CLI::success( sprintf( __( '%d件のレコードを保存しました。', 'hametuha' ), $updated ) );
		}
	}

	/**
	 * Check DB status.
	 *
	 * @return void
	 */
	public function db() {
		$query = <<<SQL
			SHOW INDEX FROM {$this->ga()->table};
SQL;
		$indices = $this->ga()->db->get_results( $query, ARRAY_A );
		if ( empty( $indices ) ) {
			\WP_CLI::error( __( 'テーブルが存在しません。', 'hametuha' ) );
		}
		$has_unique_key = false;
		$table = new Table();
		$table->setHeaders( array_keys( $indices[0] ) );
		foreach ( $indices as $index ) {
			$table->addRow( array_values( $index ) );
			if ( self::UNIQ_KEY_NAME === $index['Key_name'] ) {
				$has_unique_key = true;
			}
		}
		$table->display();
		if ( ! $has_unique_key ) {
			\WP_CLI::warning( __( 'ユニークキー制約が存在しません。作成します。', 'hametuha' ) );
			// Add uniq key index.
			$index_query = sprintf(
				'ALTER TABLE %s ADD UNIQUE KEY `%s` ( `category`, `object_id`, `calc_date`);',
				$this->ga()->table,
				self::UNIQ_KEY_NAME
			);
			$result = $this->ga()->db->query( $index_query );
			if ( ! $result ) {
				\WP_CLI::error( $this->ga()->db->last_error );
			} else {
				\WP_CLI::success( __( 'ユーニークキー制約を追加しました。テーブルは正常です。', 'hametuha' ) );
			}
			exit;
		} else {
			\WP_CLI::success( sprintf( __( 'テーブル %s は有効です。', 'hametuha' ), $this->ga()->table ) );
		}
	}

	/**
	 * Get Google Analytics Accessor.
	 *
	 * @return GoogleAnalyticsDataAccessor
	 */
	protected function ga() {
		return GoogleAnalyticsDataAccessor::get_instance();
	}
}
