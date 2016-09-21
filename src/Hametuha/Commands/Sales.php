<?php

namespace Hametuha\Commands;

use Hametuha\Model\UserSales;
use WPametu\Utility\Command;

class Sales extends Command {

	const COMMAND_NAME = 'sales';

	/**
	 * KDPの売上を保存する
	 *
	 * @synopsis [--year=<year>] [--month=<month>] [--force]
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
		if ( ! $force && false !== array_search( $year.$month, $record ) ) {
			self::e( sprintf( '%d年%d月のKDPセールスは記録済みです。', $year, $month ) );
		}
		list( $total, $success ) = UserSales::get_instance()->save_kdp_report( $year, $month );
		$record[] = $year.$month;
		update_option( 'kdp_sales_record', $record, false );
		self::s( sprintf( '%d of %d records were saved.', $success, $total ) );
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

	/**
	 * 支払い済の売上一覧を取得する
	 *
	 *
	 * @synopsis [--year=<year>] [--month=<month>] [--file=<file>]
	 *
	 * @param array $args
	 * @param array $assoc
	 */
	public function fixed( $args, $assoc ) {
		$year  = isset( $assoc['year'] ) ? $assoc['year'] : date_i18n( 'Y' );
		$month = isset( $assoc['month'] ) ? sprintf( '%02d', $assoc['month'] ) : date_i18n( 'm' );
		$file  = isset( $assoc['file'] ) ? $assoc['file'] : false;
		if ( $file && ! is_writable( dirname( $file ) ) ) {
			self::e( sprintf( '%s is not writable', $file ) );
		}
		$result = UserSales::get_instance()->get_fixed_billing( $year, $month );
		if ( ! $result ) {
			self::e( sprintf( '%d年%d月の結果はありませんでした。', $year, $month ) );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'User ID', 'deducting', 'subtotal' ] );
		$rows = [];
		foreach ( $result as $row ) {
			$rows[] = [ $row->user_id, '￥'.number_format( round( $row->deducting ) ), '￥'.number_format( round( $row->total ) ) ];
		}
		$table->setRows( $rows );
		$table->display();
		if ( $file ) {
			$csv = fopen( $file, 'w' );
			foreach ( $result as $row ) {
				// 月、日、支払い先、適用、源泉前金額、源泉額、消費税、源泉徴収後金額、住所
				switch ( $row->sales_type ) {
					case 'kdp':
						$label = '電子書籍売上：';
						break;
					case 'news':
						$label = '原稿執筆：';
						break;
					case 'task':
						$label = '作業依頼：';
						break;
					default:
						$label = '';
						break;
				}
				fputcsv( $csv, [
					mysql2date( 'm', $row->fixed ),
				    mysql2date( 'd', $row->fixed ),
				    get_user_meta( $row->user_id, '_billing_name', true ),
				    $label . $row->description,
				    round( $row->price * $row->unit ),
				    round( $row->deducting ),
				    round( $row->tax ),
				    round( $row->total ),
				    get_user_meta( $row->user_id, '_billing_address', true ),
				] );
			}
			fclose( $csv );
		}
		file_put_contents( $file, mb_convert_encoding( file_get_contents( $file ), 'sjis-win', 'utf-8' ) );
		self::s( sprintf( 'CSV out to %s', realpath( $file ) ) );
	}

}
