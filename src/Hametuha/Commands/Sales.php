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
		$table->setHeaders( [ 'Date', 'ID', 'Name', 'Sales Type', 'price', 'deducting', 'vat', 'subtotal' ] );
		$rows = [];
		foreach ( $result as $row ) {
			$user = get_userdata( $row->user_id );
			$rows[] = [
				mysql2date( 'm/d', $row->fixed ),
				$row->user_id,
				$user->user_login,
				$row->sales_type,
				'¥'.number_format( round( $row->price * $row->unit ) ),
				'¥'.number_format( round( $row->deducting ) ),
				'¥'.number_format( round( $row->tax ) ),
				'¥'.number_format( round( $row->total ) ),
			];
		}
		$table->setRows( $rows );
		$table->display();
		if ( $file ) {
			// Do CSV
			$csv_rows = [];
			foreach ( $result as $row ) {
				$key = mysql2date( 'm/d', $row->fixed );
				if ( ! isset( $csv_rows[ $key ] ) ) {
					$csv_rows[ $key ] = [];
				}
				if ( ! isset( $csv_rows[ $key ][ $row->user_id ] ) ) {
					$csv_rows[ $key ][ $row->user_id ] = [
						'before_tax' => 0,
					    'deducting'  => 0,
					    'vat'        => 0,
					    'total'      => 0,
					];
				}
				foreach ( [
					'before_tax' => $row->price * $row->unit,
					'deducting'  => $row->deducting,
					'vat'        => $row->tax,
					'total'      => $row->total,
				] as $sub_key => $amount ) {
					$csv_rows[ $key ][ $row->user_id ][ $sub_key ] += $amount;
				}
			}
			$csv = fopen( $file, 'w' );
			foreach ( $csv_rows as $date => $users ) {
				list( $m, $d ) = explode( '/', $date );
				foreach ( $users as $user_id => $record ) {
					// 月、日、支払い先、適用、源泉前金額、源泉額、消費税、源泉徴収後金額、住所
					fputcsv( $csv, [
						$m,
						$d,
						get_user_meta( $user_id, '_billing_name', true ),
						'原稿料ほか',
						round( $record['before_tax'] ),
						round( $record['deducting'] ),
						round( $record['vat'] ),
						round( $record['total'] ),
						get_user_meta( $user_id, '_billing_address', true ),
					] );
				}
			}
			fclose( $csv );
			file_put_contents( $file, mb_convert_encoding( file_get_contents( $file ), 'sjis-win', 'utf-8' ) );
			self::s( sprintf( 'CSV out to %s', realpath( $file ) ) );
		}
	}

}
