<?php

namespace Hametuha\Commands;

use cli\Table;
use Hametuha\Model\UserSales;
use Hametuha\Sharee\Models\RevenueModel;
use WPametu\Utility\Command;

/**
 * 破滅派の売上を確認するコマンド
 *
 * @package Hametuha\Commands
 */
class Sales extends Command {

	const COMMAND_NAME = 'sales';

	/**
	 * KDPの売上を保存する
	 *
	 * @synopsis [--year=<year>] [--month=<month>] [--force] [--dry-run]
	 *
	 * @param array $args
	 * @param array $assoc
	 */
	public function kdp( $args, $assoc ) {
		$record = get_option( 'kdp_sales_record', [] );
		list( $default_year, $default_month ) = explode( '-', date_i18n( 'Y-m', strtotime( 'Previous month' ) ) );
		$year = isset( $assoc['year'] ) ? sprintf( '%04d', $assoc['year'] ) : $default_year;
		$month = isset( $assoc['month'] ) ? sprintf( '%02d', $assoc['month'] ) : $default_month;
		$force = isset( $assoc['force'] ) && $assoc['force'];
		$dry_run = isset( $assoc['dry-run'] ) && $assoc['dry-run'];
		if ( $dry_run ) {
			\WP_CLI::line( 'Getting sales report. This will not saved actually.' );
			$result = UserSales::get_instance()->save_kdp_report( $year, $month, true );
			if ( ! $result ) {
				\WP_CLI::error( '売上データが見つかりませんでした。' );
			}
			$table = new Table();
			$counter = 0;
			$table->setHeaders( [ '#', '適用', 'ユーザー', '数', '小計', '源泉徴収' ] );
			foreach ( $result as $sales ) {
				$counter++;
				$table->addRow( [ $counter, $sales->label, get_the_author_meta( 'display_name', $sales->user_id ) ?: '削除されたユーザー', $sales->unit, $sales->total, $sales->deducting ] );
			}
			$table->display();
		} else {
			if ( !$force && false !== array_search( $year . $month, $record ) ) {
				self::e( sprintf( '%d年%d月のKDPセールスは記録済みです。', $year, $month ) );
			}
			list( $total, $success ) = UserSales::get_instance()->save_kdp_report( $year, $month );
			$record[] = $year . $month;
			update_option( 'kdp_sales_record', $record, false );
			self::s( sprintf( '%d of %d records were saved.', $success, $total ) );
		}
	}

	/**
	 * ニュースの売上を保存する
	 *
	 * @synopsis [--year=<year>] [--month=<month>] [--force]
	 *
	 * @param array $args
	 * @param array $assoc
	 */
	public function news( $args, $assoc ) {
		$record = get_option( 'news_sales_record', [] );
		list( $default_year, $default_month ) = explode( '-', date_i18n( 'Y-m', strtotime( 'Previous month' ) ) );
		$year = isset( $assoc['year'] ) ? sprintf( '%04d', $assoc['year'] ) : $default_year;
		$month = isset( $assoc['month'] ) ? sprintf( '%02d', $assoc['month'] ) : $default_month;
		$force = isset( $assoc['force'] ) && $assoc['force'];
		if ( ! $force && false !== array_search( $year.$month, $record ) ) {
			self::e( sprintf( '%d年%d月のニュース報酬は記録済みです。', $year, $month ) );
		}
		list( $done, $none ) = UserSales::get_instance()->save_news_report( $year, $month );
		$record[] = $year.$month;
		update_option( 'news_sales_record', $record, false );
		self::s( sprintf( '%d users got rewarded. %d don\'t.', $done, $none ) );
	}
}
