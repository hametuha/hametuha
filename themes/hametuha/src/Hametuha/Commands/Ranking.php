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
	 * Get popular posts.
	 *
	 * @synopsis [--post_type=<post_type>] [--limit=<limit>] [--start=<start>] [--end=<end>] [--author=<author>]
	 * @return void
	 */
	public function chronic( $args, $assoc ) {
		$result = $this->ga()->chronic_popularity( $assoc );
		if ( empty( $result ) ) {
			\WP_CLI::error( __( '条件に該当する記録がありません。', 'hametuha' ) );
		}
		$headers = [ 'Date' ];
		if ( ! empty( $assoc['post_type'] ) ) {
			$headers[] = 'Post Type';
		}
		if ( ! empty( $assoc['author'] ) ) {
			$headers[] = 'Author';
		}
		$headers[] = 'PV';
		$table     = new Table();
		$table->setHeaders( $headers );
		foreach ( $result as $row ) {
			$table->addRow( $row );
		}
		$table->display();
	}

	/**
	 * Check DB status.
	 *
	 * @return void
	 */
	public function db() {
		$query   = <<<SQL
			SHOW INDEX FROM {$this->ga()->table};
SQL;
		$indices = $this->ga()->db->get_results( $query, ARRAY_A );
		if ( empty( $indices ) ) {
			\WP_CLI::error( __( 'テーブルが存在しません。', 'hametuha' ) );
		}
		$has_unique_key = false;
		$table          = new Table();
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
			$result      = $this->ga()->db->query( $index_query );
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
	 * Get audience.
	 *
	 * @synopsis <group> [--start=<start>] [--end=<end>] [--author=<author>]
	 *
	 * @param array $args  Command option..
	 * @param array $assoc Arguments.
	 * @return void
	 */
	public function audience( $args, $assoc ) {
		list( $group ) = $args;
		$start         = $assoc['start'] ?? date_i18n( 'Y-m-d', strtotime( '7days ago' ) );
		$end           = $assoc['end'] ?? date_i18n( 'Y-m-d' );
		$author        = $assoc['author'] ?? 0;
		$result        = $this->ga()->audiences( $group, $start, $end, $author );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		if ( empty( $result ) ) {
			\WP_CLI::error( __( '結果がありませんでした。', 'hametuha' ) );
		}
		$headers = [];
		$indices = [ 0 ];
		switch ( $group ) {
			case 'gender':
				$headers [] = __( '性別', 'hametuha' );
				break;
			case 'generation':
				$headers [] = __( '年齢層', 'hametuha' );
				break;
			case 'new':
				$headers [] = __( '新規ユーザー', 'hametuha' );
				break;
			case 'region':
				$headers [] = __( '地域', 'hametuha' );
				break;
			case 'source':
				$headers [] = __( '参照元', 'hametuha' );
				break;
			case 'profile':
				$headers [] = __( 'プロフィールページ', 'hametuha' );
				break;
			case 'referrer':
				$headers [] = __( '貢献者', 'hametuha' );
				break;
			default:
				\WP_CLI::error( __( '無効なグループが指定されています。', 'hametuha' ) );
				break;
		}
		$headers[] = __( 'セッション数', 'hametuha' );
		$table     = new Table();
		$table->setHeaders( $headers );
		foreach ( $result as $row ) {
			$new_row = [];
			foreach ( $indices as $i ) {
				$new_row[] = $row[ $i ];
			}
			$new_row[] = $row[ count( $row ) - 1 ];
			$table->addRow( $new_row );
		}
		$table->display();
	}

	/**
	 * 2023-07-01以降のwpg_ga_rankingデータを既存の_current_pvに追加する
	 *
	 * @synopsis [--post_type=<post_type>] [--start=<start>] [--end=<end>] [--dry-run]
	 * @param array $args      Command arguments.
	 * @param array $assoc_args Command options.
	 * @return void
	 */
	public function rebuild_current_pv( $args, $assoc_args ) {
		$post_type = $assoc_args['post_type'] ?? 'post';
		$dry_run   = isset( $assoc_args['dry-run'] );

		// GA4のデータが確定するまで3日かかるため、デフォルトは3日前まで
		$three_days_ago = new \DateTime( 'now', wp_timezone() );
		$three_days_ago->modify( '-3 days' );

		$start = $assoc_args['start'] ?? '2023-07-01';
		$end   = $assoc_args['end'] ?? $three_days_ago->format( 'Y-m-d' );

		$this->add_pv_to_current( $start, $end, $post_type, $dry_run );
	}

	/**
	 * 指定期間のwpg_ga_rankingデータを既存の_current_pvに追加する
	 *
	 * @synopsis [--post_type=<post_type>] [--start=<start>] [--end=<end>] [--dry-run]
	 * @param array $args      Command arguments.
	 * @param array $assoc_args Command options.
	 * @return void
	 */
	public function update_current_pv( $args, $assoc_args ) {
		$post_type = $assoc_args['post_type'] ?? 'post';
		$dry_run   = isset( $assoc_args['dry-run'] );

		// GA4のデータが確定するまで3日かかるため、デフォルトは3日前
		$three_days_ago = new \DateTime( 'now', wp_timezone() );
		$three_days_ago->modify( '-3 days' );

		$start = $assoc_args['start'] ?? $three_days_ago->format( 'Y-m-d' );
		$end   = $assoc_args['end'] ?? $three_days_ago->format( 'Y-m-d' );

		$this->add_pv_to_current( $start, $end, $post_type, $dry_run );
	}

	/**
	 * 指定期間のwpg_ga_rankingデータを既存の_current_pvに追加する（共通処理）
	 *
	 * @param string $start     開始日（Y-m-d形式）
	 * @param string $end       終了日（Y-m-d形式）
	 * @param string $post_type 投稿タイプ
	 * @param bool   $dry_run   ドライランモード
	 * @return void
	 */
	private function add_pv_to_current( $start, $end, $post_type, $dry_run ) {
		if ( $dry_run ) {
			\WP_CLI::warning( __( 'ドライランモードで実行しています。', 'hametuha' ) );
		}

		// 指定期間のデータを集計
		$query = $this->ga()->db->prepare(
			"SELECT object_id, SUM(object_value) as total_pv
			FROM {$this->ga()->table}
			WHERE category = 'general'
			AND calc_date BETWEEN %s AND %s
			GROUP BY object_id
			HAVING total_pv > 0",
			$start,
			$end
		);

		\WP_CLI::log( sprintf( __( '%1$s から %2$s のPVデータを集計中...', 'hametuha' ), $start, $end ) );
		$ranking_data = $this->ga()->db->get_results( $query, ARRAY_A );

		if ( empty( $ranking_data ) ) {
			\WP_CLI::warning( __( '指定期間のランキングデータが見つかりませんでした。', 'hametuha' ) );
			return;
		}

		\WP_CLI::log( sprintf( __( '%d件のレコードを処理します。', 'hametuha' ), count( $ranking_data ) ) );

		$updated_count = 0;
		$skipped_count = 0;
		$progress      = \WP_CLI\Utils\make_progress_bar( __( '_current_pvを更新中', 'hametuha' ), count( $ranking_data ) );

		foreach ( $ranking_data as $row ) {
			$post_id = (int) $row['object_id'];
			$new_pv  = (int) $row['total_pv'];

			// 投稿が存在し、指定されたpost_typeか確認
			$post = get_post( $post_id );
			if ( ! $post || $post->post_type !== $post_type ) {
				++$skipped_count;
				$progress->tick();
				continue;
			}

			// 既存の_current_pvを取得
			$current_pv = (int) get_post_meta( $post_id, '_current_pv', true );

			// 指定期間のPVを加算
			$total_pv = $current_pv + $new_pv;

			if ( ! $dry_run ) {
				update_post_meta( $post_id, '_current_pv', $total_pv );
				++$updated_count;
			}

			$progress->tick();
		}

		$progress->finish();

		if ( $dry_run ) {
			\WP_CLI::success( sprintf(
				__( 'ドライラン完了: %1$d件を更新予定、%2$d件をスキップ', 'hametuha' ),
				count( $ranking_data ) - $skipped_count,
				$skipped_count
			) );
		} else {
			\WP_CLI::success( sprintf(
				__( '%1$d件の_current_pvを更新しました（%2$d件スキップ）。', 'hametuha' ),
				$updated_count,
				$skipped_count
			) );
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
